<?php
/**
 * Copyright (c) 2026 Magenx Commerce. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magenx\Sitemap\Plugin;

use Magenx\Sitemap\Model\UrlRewriter;

/**
 * Applies the storefront path shape to product sitemap items.
 */
class ProductItemUrlPlugin extends AbstractItemUrlPlugin
{
    protected function getEntityType(): string
    {
        return UrlRewriter::TYPE_PRODUCT;
    }
}
