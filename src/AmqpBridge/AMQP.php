<?php
/**
 * This file defines all the constants provided by the php-amqp library in order to have them lined up
 * All constants are defined in the global namespace so that they are accessible everywhere
 */
$potentiallyDefined = array(
    'AMQP_NOPARAM'                  => 0,
    'AMQP_JUST_CONSUME'             => 1,
    'AMQP_DURABLE'                  => 2,
    'AMQP_PASSIVE'                  => 4,
    'AMQP_EXCLUSIVE'                => 8,
    'AMQP_AUTODELETE'               => 16,
    'AMQP_INTERNAL'                 => 32,
    'AMQP_NOLOCAL'                  => 64,
    'AMQP_AUTOACK'                  => 128,
    'AMQP_IFEMPTY'                  => 256,
    'AMQP_IFUNUSED'                 => 512,
    'AMQP_MANDATORY'                => 1024,
    'AMQP_IMMEDIATE'                => 2048,
    'AMQP_MULTIPLE'                 => 4096,
    'AMQP_NOWAIT'                   => 8192,
    'AMQP_REQUEUE'                  => 16384,
    'AMQP_EX_TYPE_DIRECT'           => 'direct',
    'AMQP_EX_TYPE_FANOUT'           => 'fanout',
    'AMQP_EX_TYPE_TOPIC'            => 'topic',
    'AMQP_EX_TYPE_HEADERS'          => 'headers',
    'AMQP_OS_SOCKET_TIMEOUT_ERRNO'  => 536870947,
    'PHP_AMQP_MAX_CHANNELS'         => 256,
);

// check which constants are defined and define the missing ones
foreach ($potentiallyDefined as $constantName => $constantValue) {
    if (!defined($constantName)) {
        define($constantName, $constantValue);
    }
}