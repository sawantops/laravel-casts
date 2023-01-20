<?php

namespace GeneaLabs\LaravelCasts\View\Components;

use Illuminate\Support\Carbon;

class Date extends BaseComponent
{
    public function __construct(
        string $name,
        string $value = null,
        string $label = null,
        string $labelClasses = "",
        string $groupClasses = "",
        string $errorClasses = "",
        string $helpClasses = "",
        string $helpText = ""
    ) {
        parent::__construct($name, $value, $label, $labelClasses, $groupClasses, $errorClasses, $helpClasses, $helpText);

        if ($this->value !== null) {
            if (! $this->value instanceof Carbon) {
                $this->value = (new Carbon)->parse($this->value);
            }

            $this->value = $this->value->format("Y-m-d");
        }
    }
}
