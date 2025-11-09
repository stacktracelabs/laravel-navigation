<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use StackTrace\Navigation\Facades\Navigation;
use StackTrace\Navigation\Menu;

uses(RefreshDatabase::class);

it('should create root menu', function () {
    expect(Menu::createAsRoot())
        ->exists->toBeTrue();
});

it('should create child in the menu', function () {
    $root = Menu::createAsRoot();
    $child = $root->createChild();
    $child->refresh();

    expect($root->is($child->parent))->toBeTrue();
});

it('should create menu tree', function () {
    $root = Menu::createAsRoot();
    $root->createChild();
    $root->createChild();

    $root = Menu::query()
        ->with(['descendants'])
        ->findOrFail($root->id);

    $root = $root->toTree();

    expect($root->children)->toHaveCount(2);
});

it('should get navigation by handle', function () {
    $root = Menu::createAsRoot(['handle' => 'footer']);
    $root->createChild();
    $root->createChild();

    $navigation = Navigation::findNavigationByHandle('footer');

    expect($root->is($navigation))->toBeTrue();
});
