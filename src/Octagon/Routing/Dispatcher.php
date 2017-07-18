<?php

namespace Octagon\Routing;

use Octagon\Routing\Controller;

/**
 * Dispatcher executes the controller.
 */

class Dispatcher
{

    /**
     * Dispatch the controller.
     *
     * @param \Octagon\Core\Controller $controller
     *
     * @return \Octagon\Routing\Response
     */
    public static function dispatch(Controller $controller)
    {
        // Run controller
        $response = $controller->run();

        // Return response
        return $response;
    }

}
