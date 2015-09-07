<?php
namespace AmqpBridge;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class AMQPConnection
{
    /**
     * Default properties for connecting to an amqp broker
     *
     * @var array
     */
    protected $defaultProperties = array(
        'host'              => 'localhost',
        'port'              => 5672,
        'vhost'             => '/',
        'login'             => 'guest',
        'password'          => 'guest',
        'read_timeout'      => 0,
        'write_timeout'     => 0,
        'connect_timeout'   => 0,
        'heartbeat'         => 0,
        'keepalive'         => false,
        'channel_max'       => 0,
        'frame_max'         => 0,
        'use_amqp_ext'      => true,
    );

    /**
     * The actual usable connection data
     *
     * @var array
     */
    protected $properties = array();

    /**
     * The amqp connection
     *
     * @var \AMQPConnection|\PhpAmqpLib\Connection\AMQPStreamConnection
     */
    protected $internalConnection = null;

    public function connect()
    {
        try {
            if ($this->properties['use_amqp_ext']) {
                $this->internalConnection = new \AMQPConnection($this->properties);
                return $this->internalConnection->connect();
            } else {
                $this->internalConnection = new AMQPStreamConnection(
                    $this->properties['host'],
                    $this->properties['port'],
                    $this->properties['login'],
                    $this->properties['password'],
                    $this->properties['vhost'],
                    false,      // $insist parameter?
                    'AMQPLAIN', // $login_method?
                    null,       // $login_response?
                    'en_US',    // $locale?
                    $this->properties['connect_timeout'],
                    max($this->properties['read_timeout'], $this->properties['write_timeout']),
                    null,       // context?
                    $this->properties['keepalive'],
                    $this->properties['heartbeat']
                );
            }
        } catch (\Exception $e) {
            throw new AMQPConnectionException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        return true;
    }

    /**
     * This is a merge between the properties offered by php-amqplib and php-amqp
     * Below is a list of properties that can be used
     *
     *  host => the hostname to connect - localhost
     *  port => the port to connect - 5672
     *  vhost => the virtual host to use when connecting - /
     *  login => the username to use when connecting - guest
     *  password => the password to be used when connecting - guest
     *  read_timeout => the connection will close after x seconds of not receiving any traffic - 0 for disabling
     *  write_timeout => the connection will close after x seconds of not pushing traffic - 0 for disabling
     *  connect_timeout => the connection timeout in seconds - 0 for disabling
     *
     *  heartbeat => the heartbeat negociated between client and broker at the moment of connection - 0 to disable
     *      - this is available only in 1.6beta3 in php-amqp
     *  keepalive => enable tcpkeepalive on the socket. This is available only in the php-amqplib, will be ignored in
     *      php-amqp
     *  channel_max => the maximum number of channels that can be opened on one connection - 0 standard limit
     *      - this is only available in php-amqp version 1.6beta3
     *  frame_max => the largest frame size that the server proposes for for the connection
     *      - this is only available in php-amqp version 1.6beta3
     *
     *  use_amqp_ext => if this is enabled, it will attempt to use php-amqp extension. otherwise it will attempt to use
     *      php-amqplib. Default true
     *
     * @param array $connectionData The data used to connect
     */
    public function __construct(array $connectionData)
    {
        $this->properties = array_merge($this->defaultProperties, $connectionData);
    }

    public function disconnect()
    {
        if ($this->properties['use_amqp_ext'] == true) {
            $result = $this->internalConnection->disconnect();
        } else {
            try {
                $result = $this->internalConnection->getConnection()->close();
            } catch (\Exception $e) {
                $result = false;
            }
        }

        return $result;
    }

    public function getHost()
    {
        return $this->properties['host'];
    }

    public function getLogin()
    {
        return $this->properties['login'];
    }

    public function getPassword()
    {
        return $this->properties['password'];
    }

    public function getPort()
    {
        return $this->properties['port'];
    }

    public function getVhost()
    {
        return $this->properties['vhost'];
    }

    public function isConnected()
    {
        if ($this->properties['use_amqp_ext'] == true) {
            return $this->internalConnection->isConnected();
        } else {
            return $this->internalConnection->getConnection()->isConnected();
        }
    }

    /**
     * @throws AMQPConnectionException If the pconnect method is called on top of php-amqplib
     */
    public function pconnect()
    {
        if ($this->properties['use_amqp_ext'] == true) {
            $this->internalConnection = new \AMQPConnection($this->properties);
            return $this->internalConnection->pconnect();
        } else {
            throw new AMQPConnectionException("Persistent connections not implemented when using php-amqplib!");
        }
    }

    public function pdisconnect()
    {
        if ($this->properties['use_amqp_ext'] == true) {
            return $this->internalConnection->disconnect();
        } else {
            throw new \AMQPConnectionException("Persistent connections not implemented when using php-amqplib!");
        }
    }

    /**
     * @return bool|void
     * @throws AMQPConnectionException If the reconnect does not work if using php-amqplib
     */
    public function reconnect()
    {
        if ($this->properties['use_amqp_ext'] == true) {
            return $this->internalConnection->reconnect();
        } else {
            try {
                $this->disconnect();
                return $this->connect();
            } catch (\Exception $e) {
                throw new AMQPConnectionException($e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        }
    }

    /**
     * @return bool
     * @throws AMQPConnectionException
     */
    public function preconnect()
    {
        if ($this->properties['use_amqp_ext'] == true) {
            return $this->internalConnection->preconnect();
        } else {
            throw new AMQPConnectionException("Persistent connections not implemented when using php-amqplib!");
        }
    }

    public function setHost($host)
    {
        $this->properties['host'] = $host;
    }

    public function setLogin($login)
    {
        $this->properties['login'] = $login;
    }

    public function setPassword($password)
    {
        $this->properties['password'] = $password;
    }

    public function setPort($port)
    {
        $this->setPort($port);
    }

    public function setVhost($vhost)
    {
        $this->properties['vhost'] = $vhost;
    }

    public function setTimeout($timeout)
    {
        $this->setReadTimeout($timeout);
    }

    public function getTimeout()
    {
        return $this->getReadTimeout();
    }

    public function setReadTimeout($timeout)
    {
        $this->properties['read_timeout'] = $timeout;
    }

    public function getReadTimeout()
    {
        return $this->properties['read_timeout'];
    }

    public function setWriteTimeout($readTimeout)
    {
        $this->properties['write_timeout'] = $readTimeout;
    }

    public function getWriteTimeout()
    {
        return $this->properties['write_timeout'];
    }

    public function getUsedChannels()
    {
        if ($this->properties['use_amqp_ext'] == true) {
            return $this->getUsedChannels();
        } else {
            if (!$this->internalConnection->isConnected()) {
                return 0;
            }

            return $this->internalConnection->getConnection()->getChannelId();
        }
    }

    public function getMaxChannels()
    {
        if ($this->properties['use_amqp_ext'] == true) {
            return $this->internalConnection->getMaxChannels();
        } else {
            return null;
        }
    }

    public function getMaxFrameSize()
    {
        if ($this->properties['use_amqp_ext'] == true) {
            return $this->internalConnection->getMaxFrameSize();
        } else {
            return null;
        }
    }

    public function getHeartbeatInterval()
    {
        if ($this->properties['use_amqp_ext'] == true) {
            return $this->internalConnection->getHeartbeatInterval();
        } else {
            return null;
        }
    }

    public function isPersistent()
    {
        if ($this->properties['use_amqp_ext'] == true) {
            return $this->internalConnection->isPersistent();
        }

        return false;
    }

    public function getConnection()
    {
        return $this->internalConnection;
    }

    public function useExtension()
    {
        return $this->properties['use_amqp_ext'];
    }
}