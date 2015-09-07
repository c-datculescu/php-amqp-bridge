<?php
namespace AmqpBridge;

class AMQPChannel
{
    protected $useExtension = true;

    protected $internalConnection = null;

    protected $prefetchCount = 0;

    protected $prefetchSize = 0;

    /**
     * @var AMQPConnection
     */
    protected $incomingConnection;

    /**
     * @var \AMQPChannel|\PhpAmqpLib\Channel\AMQPChannel
     */
    protected $internalChannel = null;

    public function __construct(AMQPConnection $connection)
    {
        $this->incomingConnection = $connection;
        $this->internalConnection = $connection->getConnection();
        $this->useExtension = $connection->useExtension();
        if ($this->useExtension == true) {
            $this->internalChannel = new \AMQPChannel($this->internalConnection);
        } else {
            $this->internalChannel = $this->incomingConnection->getConnection()->channel();
        }
    }

    public function commitTrasaction()
    {
        if ($this->useExtension == true) {
            return $this->internalChannel->commitTransaction();
        } else {
            try {
                return $this->internalChannel->tx_commit();
            } catch (\Exception $e) {
                throw new AMQPChannelException($e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        }
    }

    public function isConnected()
    {
        if ($this->useExtension) {
            return $this->internalChannel->isConnected();
        } else {
            // not really the channel status, but should work for the basic translation
            return $this->internalConnection->isConnected();
        }
    }

    public function getChannelId()
    {
        return $this->internalChannel->getChannelId();
    }

    public function qos($size, $count)
    {
        if ($this->useExtension) {
            return $this->internalChannel->qos($size, $count);
        } else {
            try {
                return $this->internalChannel->basic_qos($size, $count, false);
            } catch (\Exception $e) {
                throw new AMQPChannelException($e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        }
    }

    public function rollbackTransaction()
    {
        if ($this->useExtension) {
            return $this->internalChannel->rollbackTransaction();
        } else {
            try {
                return $this->internalChannel->tx_rollback();
            } catch (\Exception $e) {
                throw new AMQPChannelException($e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        }
    }

    public function setPrefetchCount($count)
    {
        if ($this->useExtension) {
            return $this->internalChannel->setPrefetchCount($count);
        } else {
            $this->prefetchCount = $count;
            $this->prefetchSize = 0;
            return $this->qos(0, $count);
        }
    }

    public function getPrefetchCount()
    {
        if ($this->useExtension) {
            return $this->internalChannel->getPrefetchCount();
        } else {
            return $this->prefetchCount;
        }
    }

    public function setPrefetchSize($size)
    {
        if ($this->useExtension) {
            return $this->internalChannel->setPrefetchSize($size);
        } else {
            $this->prefetchCount = 0;
            $this->prefetchSize = $size;

            return $this->qos($this->prefetchSize, $this->prefetchCount);
        }
    }

    public function getPrefetchSize()
    {
        if ($this->useExtension) {
            return $this->internalChannel->getPrefetchSize();
        } else {
            return $this->prefetchSize;
        }
    }

    public function startTransaction()
    {
        if ($this->useExtension) {
            return $this->internalChannel->startTransaction();
        } else {
            return $this->internalChannel->tx_select();
        }
    }

    public function getConnection()
    {
        return $this->incomingConnection;
    }

    public function getChannel()
    {
        return $this->internalChannel;
    }

    public function basicRecover($requeue = true)
    {
        if ($this->useExtension) {
            return $this->internalChannel->basicRecover($requeue);
        } else {
            return $this->internalChannel->basic_recover($requeue);
        }
    }
}