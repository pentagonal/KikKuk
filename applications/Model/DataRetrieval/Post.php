<?php
namespace KikKuk\Model\DataRetrieval;

use Doctrine\DBAL\Driver\Statement;
use KikKuk\Utilities\Sanitation;

/**
 * Class Post
 * @package KikKuk\Model\DataRetrieval
 */
class Post extends DataRetrievalAbstract
{
    /**
     * @var string
     */
    protected $table = 'post';

    /**
     * @var array
     */
    protected $selector = 'id';

    /**
     * @var PostMetaData[]
     */
    protected $metaData;

    /**
     * {@inheritdoc}
     */
    public static function find($identifier)
    {
        if (!is_numeric($identifier) && is_string($identifier)) {
            $static = new static();
            $static = $static->where('permalink', $identifier);
            $static->selector = 'permalink';
            return $static;
        }

        return parent::find($identifier);
    }

    /**
     * Find By Permalink
     *
     * @param string $permalink
     * @return DataRetrievalAbstract|Post|null
     */
    public static function findByPermalink($permalink)
    {
        if (!is_string($permalink) || trim($permalink) == '') {
            return null;
        }

        return static::where(
            'permalink',
            $permalink
        )->fetch();
    }

    /**
     * Check if protected
     *
     * @return bool
     */
    public function isProtected()
    {
        $protected =  $this->getAttribute('protected', 0);
        return (is_numeric($protected) && $protected > 0);
    }

    /**
     * Check If Password Match
     *
     * @param string $plainPassword
     * @return bool
     */
    public function isPasswordMatch($plainPassword)
    {
        $plainPassword = Sanitation::maybeSerialize($plainPassword);
        $password =  $this->getAttribute('password', null);
        if ($password && is_string($password) && strlen($password) <> 32) {
            $id = $this->getAttribute('id');
            if (is_numeric($id) && $this->find($id)) {
                $context = $this->where('id', $id);
                $context['password'] = md5($password);
                $context->update();
            }
        }
        return (!$password || $password == md5($plainPassword));
    }

    /**
     * @param array $args
     * @return int
     * @throws \Exception
     */
    public static function createPost(array $args)
    {
        $user = new static();
        foreach ($args as $key => $value) {
            if (!$user->columnExist($key)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Column %s is not exist on table %s',
                        $key,
                        $user->getTable()
                    ),
                    E_ERROR
                );
            }
            if (in_array($key, ['permalink'])) {
                if (!is_string($value) || trim($value) == '') {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Value for %s must be as a string and not empty value',
                            $key
                        ),
                        E_USER_ERROR
                    );
                }

                $args[$key] = trim(strtolower($value));
            }
        }

        /**
         * Convert Permalink
         */
        $args['permalink'] = isset($args['permalink']) ? trim($args['permalink']) : '';
        if ($args['permalink'] == '') {
            $args['permalink'] = 'post';
        }

        if (isset($args['permalink'])) {
            $c = 0;
            $permalink = $args['permalink'];
            while (static::findByPermalink($permalink)) {
                $permalink = $args['permalink'] . '-' .$c;
                $c++;
            }
            return 0;
        }
        if (isset($args['title'])) {
            $args['title'] = !is_string($args['title'])
                ? ''
                : trim(preg_replace('/(\s)+/m', ' ', $args['title']));
        }

        if (! isset($args['password'])) {
            $args['password'] = null;
        } else {
            $args['password'] = md5(Sanitation::maybeSerialize($args['password']));
        }

        if (!isset($args['content'])) {
            $args['content'] = '';
        }

        foreach ($args as $key => $value) {
            $user[$key] = $value;
        }

        return $user->save();
    }

    /**
     * @access internal
     */
    protected function sanitizePasswordData()
    {
        if (isset($this->data['current']) && $this->data['current']->has('password')) {
            $password = $this->data['current']->get('password');
            if (!is_string($password)) {
                $password = @serialize($password);
            }
            if (strlen($password) <> 32 || preg_match('/[^a-f0-9]/', $password)) {
                $password = md5($password);
            }
            $this->data['current']['password'] = $password;
        }
    }

    /**
     * {@inheritdoc}
     * @return Statement|int|null
     */
    public function save()
    {
        $this->sanitizePasswordData();
        return parent::save();
    }

    /**
     * {@inheritdoc}
     * @return Statement|int
     */
    public function update($where = null, $with = null)
    {
        $this->sanitizePasswordData();
        return parent::update($where, $with);
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function sanitizeDatabaseWhereAttributeName($keyName)
    {
        switch ($keyName) {
            case 'permalink':
                return 'LOWER(TRIM('.$this->database->quoteIdentifier($keyName).'))';
        }

        return parent::sanitizeDatabaseWhereAttributeName($keyName);
    }

    /**
     * @return array|PostMetaData[]
     */
    public function getAllMetaData()
    {
        if (!isset($this->metaData)) {
            $this->metaData = [];
            $id = $this->getAttribute('id');
            if (is_numeric($id)) {
                $this->metaData = [];
                foreach ((array) PostMetaData::find($id)->fetchAll() as $value) {
                    $this->metaData[$value->name] = $value;
                }
            }
        }

        return $this->metaData;
    }

    /**
     * Get MetaData for User
     *
     * @param string $name
     * @return PostMetaData|null
     */
    public function getMetaData($name)
    {
        if (!is_string($name)) {
            return null;
        }
        $this->getAllMetaData();
        if (array_key_exists($name, $this->metaData)) {
            return $this->metaData[$name];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function sanitizeDatabaseQuoteValue($value)
    {
        switch ($value) {
            case 'permalink':
                    $value = trim(strtolower($value));
                break;
            case 'protected':
                    $value = empty($value) || is_numeric($value) && $value < 1 ? '0' : '1';
                break;
        }

        return parent::sanitizeDatabaseQuoteValue($value);
    }

    /**
     * {@inheritdoc}
     */
    public function __set($name, $value)
    {
        if ($name == 'permalink') {
            if (!is_string($value)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        '%s must be as a string %s given',
                        ucwords($name),
                        gettype($value)
                    )
                );
            }
            $value = trim(strtolower($value));
        }
        parent::__set($name, $value);
    }
}
