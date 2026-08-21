<?php
/**
 * Copyright (c) 2026 Magenx Commerce. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magenx\Sitemap\Plugin;

use Magenx\Sitemap\Model\UrlRewriter;

/**
 * Applies the storefront path shape to store URL (storefront root) sitemap items.
 */
class StoreUrlItemUrlPlugin extends AbstractItemUrlPlugin
{
    protected function getEntityType(): string
    {
        return UrlRewriter::TYPE_STORE;
    }
}
