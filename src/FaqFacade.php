<?php

namespace Virtualorz\Faq;

use Illuminate\Support\Facades\Facade;

/**
 * @see Virtualorz\Cate\Cate
 */
class FaqFacade extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return 'faq';
    }

}
