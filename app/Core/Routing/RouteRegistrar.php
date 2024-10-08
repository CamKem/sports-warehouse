<?php

namespace App\Core\Routing;

use App\Core\Exceptions\RouteException;
use Closure;

/**
 * Methods that can be chained onto a route definition.
 *
 * @method get(string $uri)
 * @method post(string $uri)
 * @method put(string $uri)
 * @method patch(string $uri)
 * @method delete(string $uri)
 * @method options(string $uri)
 * @method any(string $uri)
 */

class RouteRegistrar
{

    protected Router $router;
    protected Route $route;

    protected array $verbs = ['get', 'post', 'put', 'patch', 'delete', 'options', 'any'];

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function controller(string|array|Closure $controller): self
    {
        if (is_string($controller) && !class_exists($controller)) {
            throw new RouteException('Controller does not exist');
        }
        $this->route->setController($controller);
        return $this;
    }

    public function middleware(string|array $middleware): self
    {
        if (is_string($middleware)) {
            $middleware = [$middleware];
        }

        $this->route->setMiddleware($middleware);
        return $this;
    }

    // TODO: Implement the where method
    //  it will be used to add a constraint to the parameters of the route
//    public function where(array $where): self
//    {
//        $this->route->setWhere($where);
//        return $this;
//    }

    public function name(string $name): self
    {
        $this->route->setName($name);
        return $this;
    }

    public function __destruct()
    {
        $this->register();
    }

    public function register(): self
    {
        if (empty($this->route->getName())) {
            throw new RouteException('All routes must use name()');
        }
        if (empty($this->route->getController())) {
            throw new RouteException('All routes must use controller()');
        }
        $this->router->addRoute($this->route);
        return $this;
    }

    /**
     * Register a new temporary route object
     * Ensuring that the method is a valid Http method and the uri is a valid uri
     * @param $method
     * @param $parameters
     * @return RouteRegistrar
     */
    public function __call($method, $parameters): static
    {
        if (in_array($method, $this->verbs, true)) {
            // if the parameters are an array, then the first element is the URI
            if ((count($parameters) < 1) && !preg_match('/^\//', $parameters[0])) {
                throw new RouteException('Not a valid URI');
            }
            $this->route = new Route($method, $parameters[0]);
        }
        return $this;
    }

}