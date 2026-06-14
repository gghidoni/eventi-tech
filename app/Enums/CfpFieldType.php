<?php

declare(strict_types=1);

namespace App\Enums;

enum CfpFieldType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Select = 'select';
    case Multiselect = 'multiselect';
    case Checkbox = 'checkbox';
    case Url = 'url';
    case Email = 'email';
    case Number = 'number';
    case Date = 'date';
}
