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
                lf.exchange.a: ~
            queues:
                lf.queue.a:
                    bindings:
                        - { exchange: lf.exchange.a, routing_key: "a.#" }
                        - { exchange: lf.exchange.a, routing_key: "b.#" }
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
                lf.exchange.a:
                    durable: false # default is "true"
                lf.exchange.b:
                    type: direct # default is "topic"
                lf.exchange.c: ~
            queues:
                lf.queue.a:
                    durable: false # default is "true"
                    bindings:
                        - { exchange: lf.exchange.a, routing_key: "a.#" }
                        - { exchange: lf.exchange.b, routing_key: "b.#" }
                lf.queue.b:
                    bindings:
                        - { exchange: lf.exchange.a, routing_key: "a.#" }
                        - { exchange: lf.exchange.b, routing_key: "b.#" }
                        - { exchange: lf.exchange.c, routing_key: "c.#" }
                lf.queue.c:
                    bindings:
                        - { exchange: lf.exchange.a, routing_key: "a.#" }
                        - { exchange: lf.exchange.c, routing_key: "c.#" }

```
