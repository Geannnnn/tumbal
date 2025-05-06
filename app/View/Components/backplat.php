<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class backplat extends Component
{
    public $title;
    public $subtitle;
    public $search;

    public function __construct($title, $subtitle, $search = true)
    {
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->search = $search;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.backplat');
    }
}
