<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    /**
     * Whether the layout should render full-page (no centered container).
     */
    public $full;

    /**
     * Create a new component instance.
     */
    public function __construct($full = false)
    {
        $this->full = filter_var($full, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
