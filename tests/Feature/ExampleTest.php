<?php

test('login page is available to guests', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});
