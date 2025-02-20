<?php

uses()->group('quick-page-controller');

it('quick page', function () {
    asAdmin()->get('/quick-page')
        ->assertOk()
        ->assertSee('Quick page')
        ->assertSee('@fragment_updated:_content')
        ->assertSee('/quick-page?_fragment-load=_content')
        ->assertSee('var1')
        ->assertSee('var2')
    ;
});
