# RabbitMqAdminToolkitBundle

[![Build Status](https://travis-ci.org/olaurendeau/RabbitMqAdminToolkitBundle.svg?branch=master)](https://travis-ci.org/olaurendeau/RabbitMqAdminToolkitBundle) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/olaurendeau/RabbitMqAdminToolkitBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/olaurendeau/RabbitMqAdminToolkitBundle/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/olaurendeau/RabbitMqAdminToolkitBundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/olaurendeau/RabbitMqAdminToolkitBundle/?branch=master)

Automate rabbitmq vhost's configuration creation / update

## Installation

Add RabbitMqAdminToolkitBundle to your composer.json, then update

```json
{
    ...
    "require": {
        "olaurendeau/rabbit-mq-admin-toolkit-bundle": "~1.0"
    },
    ...
}
```
Add RabbitMqAdminToolkitBundle to your application kernel

```php
    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Ola\RabbitMqAdminToolkitBundle\OlaRabbitMqAdminToolkitBundle(),
            // ...
        );
    }
```

Update your configuration

```yml
# app/config/config.yml
ola_rabbit_mq_admin_toolkit:
    delete_allowed: true # Allow deletion of exchange, queues and binding for updating configuration. Shouldn't be enabled in production
    connections:
        default: http://user:password@localhost:15672
    vhosts:
        default:
            name: /my_vhost
            permissions:
                user: ~
            exchanges:
                exchange.a: ~
            queues:
                queue.a:
                    bindings:
                        - { exchange: exchange.a, routing_key: "a.#" }
                        - { exchange: exchange.a, routing_key: "b.#" }
```

## Usage

Simply run `app/console rabbitmq:vhost:define`.

## Configuration sample

See `app/console config:dump-reference OlaRabbitMqAdminToolkitBundle` for full configuration possibilities

```yml
# app/config/config.yml
ola_rabbit_mq_admin_toolkit:
    delete_allowed: true # Allow deletion of exchange, queues and binding for updating configuration. Shouldn't be enabled in production
    default_vhost: test # default is "default"
    silent_failure: true # Catch all exceptions in commands. Could be use in test environment if no rabbitmq available
    connections:
        default: http://user:password@localhost:15672
        vm: http://user:password@192.168.1.1:15672
    vhosts:
        test:
            name: /test
            connection: vm # default is "default"
            permissions:
                user: ~
            exchanges:
                exchange.a:
                    durable: false # default is "true"
                exchange.b:
                    type: direct # default is "topic"
                exchange.c: ~
            queues:
                queue.a:
                    durable: false # default is "true"
                    bindings:
                        - { exchange: exchange.a, routing_key: "a.#" }
                        - { exchange: exchange.b, routing_key: "b.#" }
                queue.b:
                    bindings:
                        - { exchange: exchange.a, routing_key: "a.#" }
                        - { exchange: exchange.b, routing_key: "b.#" }
                        - { exchange: exchange.c, routing_key: "c.#" }
                queue.c:
                    bindings:
                        - { exchange: exchange.a, routing_key: "a.#" }
                        - { exchange: exchange.c, routing_key: "c.#" }

```

## Sharding queues

Sharding queues can be usefull for processing huge amount of messages.

```yml
# app/config/config.yml
ola_rabbit_mq_admin_toolkit:
    delete_allowed: true # Allow deletion of exchange, queues and binding for updating configuration. Shouldn't be enabled in production
    connections:
        default: http://user:password@localhost:15672
    vhosts:
        default:
            name: /my_vhost
            permissions:
                user: ~
            exchanges:
                exchange.a: ~
            queues:
                queue.a.sharded:
                    name: "queue.a.{modulus}"
                    modulus: 5
                    bindings:
                        - { exchange: exchange.a, routing_key: "a.{modulus}.#" }
                        - { exchange: exchange.a, routing_key: "b.#" }
```
