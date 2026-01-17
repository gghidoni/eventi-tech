---
name: livewire-component
description: Crea componenti Livewire seguendo le convenzioni del progetto
---
## Creazione Componenti Livewire

### Struttura
- Classe: `app/Livewire/NomeComponente.php`
- Vista: `resources/views/livewire/nome-componente.blade.php`

### Template Classe
```php
<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;

final class NomeComponente extends Component
{
    public function render()
    {
        return view('livewire.nome-componente');
    }
}
```

### Convenzioni
- Usa `final class` per i componenti
- Typed properties per le proprieta pubbliche
- Form Objects per form complessi
- Actions per logica di business
