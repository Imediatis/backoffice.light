<?php

namespace Digitalis\Core\Models\ViewModels;

/**
 *
 * @author Sylvin
 */
interface ViewModelInterface
{
    public function toArray();
    public function convertToEntity();
    public static function buildFromEntity($entitySource = null);
}
