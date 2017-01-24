<?php
/**
 * Created by PhpStorm.
 * User: dilsonrabelo
 * Date: 23/01/17
 * Time: 15:33
 */

namespace Slim3\Log\Handlers;

use Illuminate\Database\Capsule\Manager;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class MoloquentHandlers extends AbstractProcessingHandler
{
    /**
     * @var array
     */
    private $settings;

    public function __construct(array $settings, $level = Logger::DEBUG, $bubble = true)
    {
        $this->settings = $settings;
        parent::__construct($level, $bubble);
    }

    protected function write(array $record)
    {
        $options = $this->settings['options'];
        $optionsModel = $options['model'];

        $databaseConnection = $options['db'];

        $capsule = new Manager();
        $capsule->addConnection($databaseConnection);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        $capsule->getDatabaseManager()->extend('mongodb', function ($config) {
            return new \Moloquent\Connection($config);
        });

        $model = new $optionsModel();

        $model->channel = $record['channel'];
        $model->level = $record['level'];
        $model->message = $record['message'];
        $model->time = new \DateTime('now');

        $model->save();
        $capsule = null;
    }
}