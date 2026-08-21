<?php
/**
 * Copyright (c) 2026 Magenx Commerce. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magenx\Sitemap\Model;

use Magento\Sitemap\Model\SitemapItemInterface;

/**
 * Decorates a core sitemap item with a rewritten URL.
 *
 * A decorator rather than a second SitemapItemInterfaceFactory call, so the
 * module never depends on the core item constructor's parameter names.
 */
class PrefixedSitemapItem implements SitemapItemInterface
{
    public function __construct(
        private readonly SitemapItemInterface $item,
        private readonly string $url
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @inheritdoc
     */
    public function getPriority()
    {
        return $this->item->getPriority();
    }

    /**
     * @inheritdoc
     */
    public function getChangeFrequency()
    {
        return $this->item->getChangeFrequency();
    }

    /**
     * @inheritdoc
     */
    public function getImages()
    {
        return $this->item->getImages();
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt()
    {
        return $this->item->getUpdatedAt();
    }
}
