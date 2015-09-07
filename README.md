# AMQPBridge

This project is dealing with implementing a bridge between [php-amqplib](https://github.com/videlalvaro/php-amqplib) and
the OOP interface of [php-amqp](https://github.com/pdezwart/php-amqp).

## Purpose of the project

The purpose of the project is to bring in a similar interface support for heartbeats, publisher confirms and so on (which
the extension does not really support at the moment) and which are critical for running a [RabbitMQ](http://www.rabbitmq.com)
cluster in production.

## Technical considerations
As this is a wrapper for both libraries, both of them can be run independently when needed. A flag switches the used
underlying infrastructure. In this way, the publishers can benefit from the advantages of the publisher confirms, while 
a consumer can benefit from the advantages of rabbitmq-c implementations of a heartbeat.

This library has no dependencies, but recommends both libraries for obvious reasons.