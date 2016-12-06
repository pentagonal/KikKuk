<?php
namespace KikKuk\Model\DataRetrieval;

use Doctrine\DBAL\Driver\Statement;
use KikKuk\Utilities\Validation;
use Pentagonal\Phpass\PasswordHash;

/**
 * Class User
 * @package KikKuk\Model
 */
class User extends DataRetrievalAbstract
{
    /**
     * @var string
     */
    protected $table = 'user';

    /**
     * @var array
     */
    protected $selector = 'id';

    /**
     * @var UserMetaData[]
     */
    protected $metaData;

    /**
     * {@inheritdoc}
     */
    public static function find($identifier)
    {
        if (!is_numeric($identifier) && is_string($identifier)) {
            $static = new static();
            $static = $static->where('username', $identifier);
            $static->selector = 'username';
            return $static;
        }

        return parent::find($identifier);
    }

    /**
     * Find By user
     *
     * @param string $userName
     * @return DataRetrievalAbstract|User|null
     */
    public static function findByUserName($userName)
    {
        if (!is_string($userName) || trim($userName) == '') {
            return null;
        }

        return static::where(
            'username',
            strtolower(trim($userName))
        )->fetch();
    }

    /**
     * Find By user
     *
     * @param string $email
     * @return DataRetrievalAbstract|User|null
     */
    public static function findByEmail($email)
    {
        if (!is_string($email) || trim($email) == '') {
            return null;
        }

        return static::where(
            'email',
            strtolower(trim($email))
        )->fetch();
    }

    /**
     * Check If Password Match
     *
     * @param string $plainPassword
     * @return bool
     */
    public function isPasswordMatch($plainPassword)
    {
        $storedPassword =  $this->getAttribute('password', null);
        if ($storedPassword == '') {
            if (is_numeric($this->getAttribute('id'))
                && $this->find($this->getAttribute('id'))
            ) {
                $passwordHash = new PasswordHash();
                $context = $this->where('id', $this->getAttribute('id'));
                $newHash = $passwordHash->hashPassword(sha1(microtime() . KIK_KUK_HASH));
                $context['password'] = $newHash;
                $context->update();
            }

            return false;
        }

        if (!is_string($plainPassword) || trim($plainPassword) == '') {
            return false;
        }

        $passwordHash = new PasswordHash();
        return $passwordHash->checkPassword(sha1($plainPassword), $storedPassword);
    }

    /**
     * Check if with username
     *
     * @param string $userName
     * @return bool|null
     */
    public static function userExist($userName)
    {
        return (bool) static::findByUserName($userName);
    }

    /**
     * Check if user with certain Email Exists
     *
     * @param string $email
     * @return bool|null
     */
    public static function emailExist($email)
    {
        return (bool) static::findByEmail($email);
    }

    /**
     * @param array $args
     * @return int
     * @throws \Exception
     */
    public static function createUser(array $args)
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
            if (in_array($key, ['username', 'email'])) {
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

        if (isset($args['username']) && static::userExist($args['username'])) {
            return 0;
        }

        if (isset($args['email']) && static::emailExist($args['email'])) {
            return 0;
        }

        if (!isset($args['password'])) {
            $args['password'] = md5(microtime() . KIK_KUK_HASH);
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

            /**
             * Check If PasswordHash
             */
            if (! Validation::isMaybePasswordHash($password)) {
                $passwordHash = new PasswordHash();
                $password = $passwordHash->hash(sha1($password));
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
        if (isset($this->data['current']['username'])) {
            $this->data['current']->set('username', strtolower($this->data['current']['username']));
        }

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
            case 'username':
            case 'email':
                return 'LOWER(TRIM('.$this->database->quoteIdentifier($keyName).'))';
        }

        return parent::sanitizeDatabaseWhereAttributeName($keyName);
    }

    /**
     * @return array|UserMetaData[]
     */
    public function getAllMetaData()
    {
        if (!isset($this->metaData)) {
            $this->metaData = [];
            $id = $this->getAttribute('id');
            if (is_numeric($id)) {
                $this->metaData = [];
                foreach ((array) UserMetaData::find($id)->fetchAll() as $value) {
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
     * @return UserMetaData|null
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
            case 'username':
            case 'email':
                $value = trim(strtolower($value));
        }

        return parent::sanitizeDatabaseQuoteValue($value);
    }

    /**
     * {@inheritdoc}
     */
    public function __set($name, $value)
    {
        if ($name == 'username') {
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
