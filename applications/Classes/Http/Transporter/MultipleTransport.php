<?php
namespace KikKuk\Http\Transporter;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use Psr\Http\Message\RequestInterface;

/**
 * Class MultipleTransport
 * @package KikKuk\Http\Transporter
 */
class MultipleTransport
{
    /**
     * @var RequestInterface[]
     */
    protected $requests = [];

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * MultipleTransport constructor.
     * @param Transport|null $transport
     */
    public function __construct(Transport $transport = null)
    {
        if (is_null($transport)) {
            $this->client = new Client();
            return;
        }
        $this->client = $transport->getClient();
    }

    /**
     * Add Transport Request
     *
     * @param Transport $transport
     * @return MultipleTransport
     */
    public function add(Transport $transport)
    {
        $this->requests[] = $transport->getRequest();
        return $this;
    }

    /**
     * Clearing The Request
     *
     * @return MultipleTransport
     */
    public function clear()
    {
        $this->requests = [];
        return $this;
    }

    /**
     * Remove Request
     *
     * @param int|string $position integer keyname or Domain name parse
     * @return MultipleTransport
     */
    public function remove($position)
    {
        if (empty($this->requests)) {
            return $this;
        }
        if (is_numeric($position)) {
            unset($this->requests[$position]);
        } elseif (is_string($position)) {
            $position = trim(strtolower($position));
            if ($position == '') {
                return $this;
            }
            $changed = false;
            foreach ($this->requests as $key => $request) {
                if ($request->getUri() == $position) {
                    $changed = true;
                    unset($this->requests[$key]);
                }
            }
            if ($changed) {
                $this->requests = array_values($this->requests);
            }
        }
        return $this;
    }

    /**
     * Sending Transport Batch Pool
     *
     * @param null|transport|client $transport
     * @return array
     */
    public function send($transport = null)
    {
        if (empty($this->requests)) {
            return null;
        }
        if (!is_null($transport)) {
            if ($transport instanceof Client) {
                $this->client = $transport;
            } elseif ($transport instanceof Transport) {
                $this->client = $transport->getClient();
            }
        }

        return Pool::batch(
            $this->client,
            $this->requests
        );
    }

    /**
     * Create New Object instance
     *
     * @return MultipleTransport
     */
    public static function create()
    {
        return new static;
    }
}
