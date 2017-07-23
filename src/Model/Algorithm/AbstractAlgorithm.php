<?php
/**
 * File: AbstractAlgorithm.php
 *
 * @author      Maciej Sławik <maciekslawik@gmail.com>
 * Github:      https://github.com/maciejslawik
 */

namespace MSlwk\RoutePlanner\Model\Algorithm;

class AbstractAlgorithm
{
    /**
     * @var resource
     */
    protected $dbConnection;

    /**
     * AbstractAlgorithm constructor.
     *
     * @param resource $dbConnection
     */
    public function __construct(resource $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }
}
