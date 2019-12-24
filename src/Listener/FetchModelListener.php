<?php
declare(strict_types=1);


namespace CC\Hyperf\Common\Listener;


use Hyperf\Database\Events\StatementPrepared;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

/**
 * Class FetchModelListener
 * @package CC\Hyperf\Common\Listener
 * @Listener
 */
class FetchModelListener implements ListenerInterface
{

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            StatementPrepared::class
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     * @param object $event
     */
    public function process(object $event)
    {
        if ($event instanceof StatementPrepared) {
            $event->statement->setFetchMode(\PDO::FETCH_ASSOC);
        }
    }
}