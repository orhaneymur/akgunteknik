<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Core\Traits\ApiResponse;

/**
 * Base controller for all module HTTP controllers.
 */
abstract class BaseController extends Controller
{
    use ApiResponse;
}

