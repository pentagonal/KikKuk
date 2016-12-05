<?php
namespace KikKuk\Utilities;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaConfig;
use Doctrine\DBAL\Schema\Table;
use KikKuk\Database;
use KikKuk;

/**
 * Class DatabaseUtility
 * @package KikKuk\Utilities
 */
class DatabaseUtility
{
    /**
     * Quote Database values
     *
     * @param mixed $args
     * @return array|mixed|string
     */
    public static function quote($args)
    {
        /** @var Database $database */
        $database = KikKuk::get('database');
        if (!$database instanceof Database) {
            throw new \RuntimeException(
                'Database connection container has been override !',
                E_COMPILE_ERROR
            );
        }
        return $database->quotes($args);
    }

    /**
     * @param array $structures
     */
    public static function execSchema(array $structures)
    {
        $database = KikKuk::get('database');
        if (!$database instanceof Database) {
            throw new \RuntimeException(
                'Database connection container has been override !',
                E_COMPILE_ERROR
            );
        }

        /**
         * @var \Doctrine\DBAL\Connection
         */
        $scheme = $database->getSchemaManager();
        foreach ($structures as $key => $definitions) {
            unset($structures[$key]);
            $structures[$database->prefix($key, false)] = $definitions;
        }

        /**
         * Looping Create Tables
         */
        $arr_table = [];
        foreach ($structures as $tableName => $table) {
            if (empty($table['columns']) || !is_array($table['columns']) || empty($table['columns'])) {
                continue;
            }
            $doctrineTable = new Table($tableName);
            foreach ($table['columns'] as $key => $value) {
                if (empty($value['type'])) {
                    throw new \RuntimeException(
                        'Invalid Database Scheme, Schema Type does not exists',
                        E_USER_ERROR
                    );
                }
                $value['options'] = ! isset($value['options']) ? [] : $value['options'];
                $doctrineTable->addColumn($key, $value['type'], $value['options']);
            }
            $table['properties'] = ! empty($table['properties'])
            && is_array($table['properties'])
                ? $table['properties']
                : [];
            foreach ($table['properties'] as $key => $value) {
                if (method_exists($doctrineTable, "set{$key}")) {
                    $method = "set{$key}";
                    if (is_array($value) && !empty($value['args']) && is_array($value['args'])) {
                        $value = $value['args'];
                    } else {
                        $value = [$value];
                    }
                    call_user_func_array([$doctrineTable, $method], $value);
                } elseif (method_exists($doctrineTable, "add{$key}")) {
                    $method = "add{$key}";
                    if (is_array($value) && !empty($value['args']) && is_array($value['args'])) {
                        $value = $value['args'];
                    } else {
                        $value = [$value];
                    }
                    call_user_func_array([$doctrineTable, $method], $value);
                }
            }
            $arr_table[] = $doctrineTable;
        }

        /**
         * If is Not Empty
         */
        if (!empty($arr_table)) {
            /**
             * Build Schema Config
             */
            $doctrineSchema = new SchemaConfig();
            $doctrineSchema->setDefaultTableOptions([
                'collate' => 'utf8_unicode_ci', # use utf8 unicode ci more accurate of language data sent
                'charset' => 'utf8'
            ]);
            $doctrineSchema = new Schema($arr_table, [], $doctrineSchema);
            /**
             * Execute Creating Tables
             */
            foreach ($doctrineSchema->getTables() as $key => $value) {
                $scheme->createTable($value);
            }
        }
    }
}
