<?php

/**
 * Действие по для модификатора буферазации
 *
 * @author morph
 */
class Event_Slot_Modifier_Buffering extends Event_Slot
{
    /**
     * @inheritdoc
     */
    public function action()
    {
        $content = null;
        $params = $this->getParams();
        if (ob_get_status()) {
            $content = ob_get_contents();
            ob_end_clean();
        }
        if ($params['file']) {
            file_put_contents(IcEngine::root() . $params['file'], $content);
        }
    }
}