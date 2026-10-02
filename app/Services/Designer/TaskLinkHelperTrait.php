<?php

namespace App\Services\Designer;

trait TaskLinkHelperTrait
{
    protected function taskLinkForCreator($task)
    {
        return route('strategist.tasks.show', $task);
    }
}

