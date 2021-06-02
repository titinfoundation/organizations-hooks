<?php

namespace Directus\Custom\Hooks\Organizations;

use Directus\Hook\HookInterface;

class OrganizationsEmails implements HookInterface
{
  public function handle(array $data = null)
  {
      // set the product sku before insert
      // $payload->set('sku', 'value');

      // make sure to return the payload
      return "THis is a test from classs";
  }
}








