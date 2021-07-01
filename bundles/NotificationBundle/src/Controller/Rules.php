<?php

namespace corite\NotificationBundle\Controller;

use Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Rules
{
    private string $operator;

    private $conditions;

    private $effects;

    public function __construct()
    {
        $logger = new Logger('notification-service');
        $logger->pushHandler(new StreamHandler('../var/log/notifications.log', Logger::DEBUG));


        if (is_file(__DIR__.'/../../config/rules.json')) {
            $rules = file_get_contents(__DIR__.'/../../config/rules.json');
        } else {
            throw new Exception('There is problem with file rules.json');
        }

        $rules = json_decode($rules, false);
        $this->operator = $rules->rules[0]->operator;
        $this->conditions = $rules->rules[0]->conditions;
        $this->effects = $rules->rules[0]->effects;
    }

    public function getEffects() {
        return $this->effects;
    }

    private function compareWithCondition($first, $second, $condition)
    {
        switch ($condition) {
            case 'equal':
                return $first === $second;
            case 'inArray':
                return in_array($second, $first);
            case 'moreThan':
                return $first > $second;
            case 'lessThan':
                return $first < $second;
            default:
                return "error";
        }
    }

    private function operatorResult($first, $second)
    {
        if ($this->operator === 'and') {
            return $first and $second;
        } elseif ($this->operator === 'or') {
            return $first or $second;
        } else {
            return "error";
        }
    }

    private function operatorInitial()
    {
        if ($this->operator === 'and') {
            return true;
        } elseif ($this->operator === 'or') {
            return false;
        } else {
            return "error";
        }
    }

    public function isConditionsTrue($project)
    {
        return array_reduce($this->conditions, function ($acc, $condition) use ($project) {

            $conditionKey = $condition->key;
            $conditionVal = $condition->val;
            $conditionCondition = $condition->condition;

            return $this->operatorResult($acc, $this->compareWithCondition($project->$conditionKey, $conditionVal, $conditionCondition));

        }, $this->operatorInitial());
    }

}