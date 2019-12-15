<?php declare(strict_types=1);

namespace Framework;

final class EventSystem
{
    /** @var array */
    private $events;

    /**
     * Bind an event to closure
     * @param  String  $event
     * @param  \Closure $callback
     * @param  Integer $priority
     * @return void
     */
    public function on($event, $callback, $priority = 0): self
    {
        if (\is_array($event)) {
            foreach ($event as &$evt) {
                $this->on($evt, $callback, $priority);
            }
            return $this;
        }

        if (!isset($this->events[$event])) {
            $this->events[$event] = [];
        }

        $this->events[$event][] = ['fn' => $callback, 'prio' => $priority];

        return $this;
    }

    public function trigger(string $event, array $params=[]): self
    {
        if (!isset($this->events[$event])){
            return $this;
        }

        if (!\count($this->events[$event])){
            return $this;
        }

        $queue = new \SplPriorityQueue();

        foreach ($this->events[$event] as $index => $action){
            $queue->insert($index, $action['prio']);
        }

        $queue->top();

        while ($queue->valid()){
            $index = $queue->current();
            if (\is_callable($this->events[$event][$index]['fn'])){
                if (\call_user_func_array($this->events[$event][$index]['fn'], $params) === false) {
                    break; // stop Propagation
                }
            }
            $queue->next();
        }

        return $this;
    }
}
