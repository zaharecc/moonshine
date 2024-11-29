<?php

declare(strict_types=1);

namespace MoonShine\AssetManager\Traits;

trait HasLink
{
    private string $link;

    public function link(string $link): static
    {
        $this->link = $link;

        return $this;
    }

    public function getLink(): string
    {
        $link = $this->link;

        if (! str_contains($link, '&v=') && ! str_contains($link, '?v=') && ! \is_null($this->getVersion())) {
            $separator = str_contains($link, '?') ? '&' : '?';

            return $link . $separator . 'v=' . $this->getVersion();
        }

        return $link;
    }
}
