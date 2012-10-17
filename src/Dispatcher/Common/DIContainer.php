<?php
namespace Dispatcher;

use Closure;
use InvalidArgumentException;

/**
 * This is a copy cat of Pimple {@link http://pimple.sensiolabs.org/}.
 * <code>
 * Usage:
 *
 * $container = new Container();
 * $container['myKey'] = 'somekey';
 *
 * $container['logger'] = function($container) {
 *     return new DBLogger;
 * };
 *
 * // shared container will returns the same instance for multiple call
 * $container->share('anotherLogger', function($container) {
 *     return new DBLogger($container['myKey']);
 * });
 *
 * // spl_object_hash($obj) === spl_object_hash($another);
 * $obj = $container['anotherLogger'];
 * $another = $container['anotherLogger'];
 * </code>
 */
class DIContainer implements \ArrayAccess
{
    /**
     * @var array
     */
    private $deps;

    /**
     * Creates an instance with the given dependencies.
     * @param array $deps The associative array of the dependencies
     */
    public function __construct (array $deps = array())
    {
        $this->deps = $deps;
    }

    /**
     * Sets the dependency with the specified $key in the associative array form.
     * @param string $key   The name of the dependency
     * @param mixed  $value The dependency, which can be an anonymous function or mixed
     */
    public function offsetSet($key, $value)
    {
        $this->deps[$key] = $value;
    }

    /**
     * Gets the dependency with the specified $key in the associative array form.
     * @param  string $key The name of the dependency
     * @return mixed       Returns the dependency
     * @throws \InvalidArgumentException If dependency is not found in the container
     */
    public function offsetGet($key)
    {
        if (!array_key_exists($key, $this->deps)) {
            throw new InvalidArgumentException('Unable to get '.$key);
        }

        $value = $this->deps[$key];
        return is_callable($this->deps[$key]) ? $value($this) : $value;
    }

    /**
     * Checks whether the dependency is in the container.
     * @param string $key The name of the dependency
     * @return boolean    TRUE if found, otherwise, FALSE
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->deps);
    }

    /**
     * Removes the dependency with the given $key.
     * @param string $key
     */
    public function offsetUnset($key)
    {
        unset($this->deps[$key]);
    }

    /**
     * Shares the dependency with multiple call.
     * @param string  $key      The name of the dependency
     * @param Closure $callable The dependency
     */
    public function share($key, Closure $callable)
    {
        $this->deps[$key] = function ($container) use ($callable) {
            static $obj;
            if ($obj === NULL) {
                $obj = $callable($container);
            }
            return $obj;
        };
    }
}
