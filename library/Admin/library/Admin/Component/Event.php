<?php

class Admin_Component_Event extends Admin_Component_Abstract {

    public function prepare() {
        $event = $this->_params->getEvent('event');
        $venue = $event->getVenue();

        if ($songListSuggested = $event->getSongListSuggested()) {
            $songListSuggested->setPage(1, 3);
        }

        $this->setTplParam('event', $event);
        $this->setTplParam('venue', $venue);
        $this->setTplParam('songListSuggested', $songListSuggested);
    }
}
