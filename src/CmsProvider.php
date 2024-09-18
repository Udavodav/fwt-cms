<?php

namespace Fwt\Crud;

use Illuminate\Support\ServiceProvider;

class CmsProvider extends ServiceProvider
{

    public function boot(): void
    {
        $this->commands(MakeCrudCommand::class);
    }

}
