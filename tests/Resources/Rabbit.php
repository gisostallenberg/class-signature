<?php
class Rabbit extends Mamal implements Chasable {

    public function beingChased(Chaser $chaser)
    {
        return sprintf('Help! I\'m being chased by a %s!', get_class($chaser));
    }
}