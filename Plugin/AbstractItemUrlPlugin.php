<?php
/**
 * Copyright (c) 2026 Magenx Commerce. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magenx\Sitemap\Plugin;

use Magenx\Sitemap\Model\Config;
use Magenx\Sitemap\Model\PrefixedSitemapItemFactory;
use Magenx\Sitemap\Model\UrlRewriter;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\SitemapItemInterface;

/**
 * Rewrites the URL of every sitemap item a core item provider returns.
 *
 * `Magento\Sitemap\Model\ItemProvider\*::getItems()` is the only public seam
 * in sitemap generation — `Sitemap::_getUrl()` and `_getStoreBaseUrl()` are
 * protected — so the storefront path shape is applied here, one plugin per
 * entity type because the item itself does not say what it describes.
 */
abstract class AbstractItemUrlPlugin
{
    public function __construct(
        private readonly Config $config,
        private readonly UrlRewriter $urlRewriter,
        private readonly PrefixedSitemapItemFactory $itemFactory
    ) {
    }

    /**
     * One of the UrlRewriter::TYPE_* constants.
     */
    abstract protected function getEntityType(): string;

    /**
     * @param ItemProviderInterface $subject
     * @param SitemapItemInterface[] $result
     * @param int $storeId
     * @return SitemapItemInterface[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetItems(ItemProviderInterface $subject, array $result, $storeId): array
    {
        $storeId = (int)$storeId;

        if (!$this->config->isEnabled($storeId)) {
            return $result;
        }

        $entityType = $this->getEntityType();
        $items = [];

        foreach ($result as $item) {
            if (!$item instanceof SitemapItemInterface) {
                $items[] = $item;
                continue;
            }

            $url = (string)$item->getUrl();
            $rewritten = $this->urlRewriter->rewrite($url, $entityType, $storeId);

            $items[] = $rewritten === $url
                ? $item
                : $this->itemFactory->create(['item' => $item, 'url' => $rewritten]);
        }

        return $items;
    }
}
