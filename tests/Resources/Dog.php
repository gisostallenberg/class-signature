<?php
class Dog extends Mamal implements Chaser {
    private $lastBark;

    public $name;

    protected $barked = false;

    public function chase(Chasable $chasable)
    {
        return sprintf('I\'m chasing a %s!', get_class($chasable));
    }

    public function bark(array $possibleBarks = array('Bark!'), $amount = 1)
    {
        $this->storeBarked(true);
        foreach (range(0, $amount) as $time) {
            echo $this->setLastBark(array_rand($possibleBarks) );
        }
    }

    protected function storeBarked($didBark = true)
    {
        $this->barked = $didBark;
    }

    private function setLastBark($bark) {
        $this->lastBark = $bark;

        return $this->lastBark;
    }
}
