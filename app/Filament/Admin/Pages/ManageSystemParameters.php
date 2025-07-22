<?php

    namespace App\Filament\Admin\Pages;

    use App\Models\SystemParameter;
    use Filament\Forms\Components\KeyValue;
    use Filament\Forms\Components\Placeholder;
    use Filament\Forms\Components\Select;
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Components\Toggle;
    use Filament\Forms\Form;
    use Filament\Notifications\Notification;
    use Filament\Pages\Page;
    use Illuminate\Support\Facades\DB;

    class ManageSystemParameters extends Page
    {
        protected static ?string $navigationIcon = 'heroicon-o-cog';
        protected static string $view = 'filament.admin.pages.manage-system-parameters';
        protected static ?string $navigationGroup = 'Configuration Système';
        protected static ?int $navigationSort = 100;
        protected static ?string $title = 'Paramètres Système';

        public array $data = [];

        public function mount(): void
        {
            $parameters = SystemParameter::all();
            foreach ($parameters as $param) {
                $this->data[$param->key] = $param->value;
            }
            $this->form->fill($this->data);
        }

        public function form(Form $form): Form
        {
            $fields = [];
            $parameters = SystemParameter::all();

            foreach ($parameters as $param) {
                $field = null;
                switch ($param->type) {
                    case 'int':
                        $field = TextInput::make($param->key)
                            ->label($param->key)
                            ->numeric()
                            ->default($param->value);
                        break;
                    case 'boolean':
                        $field = Toggle::make($param->key)
                            ->label($param->key)
                            ->default((bool)$param->value);
                        break;
                    case 'json':
                        $field = KeyValue::make($param->key)
                            ->label($param->key)
                            ->default(json_decode($param->value, true));
                        break;
                    default: // string
                        $field = TextInput::make($param->key)
                            ->label($param->key)
                            ->default($param->value);
                        break;
                }

                if ($field) {
                    $fields[] = $field->helperText($param->description);
                }
            }

            return $form->schema($fields);
        }

        public function save(): void
        {
            try {
                DB::beginTransaction();

                $data = $this->form->getState();

                foreach ($data as $key => $value) {
                    $param = SystemParameter::where('key', $key)->first();
                    if ($param) {
                        // Cast value back to string for storage, as SystemParameter::getValue handles retrieval casting
                        if ($param->type === 'json') {
                            $param->value = json_encode($value);
                        } elseif ($param->type === 'boolean') {
                            $param->value = (string)(int)$value;
                        } else {
                            $param->value = (string)$value;
                        }
                        $param->save();
                    }
                }

                DB::commit();

                Notification::make()
                    ->title('Paramètres système mis à jour avec succès.')
                    ->success()
                    ->send();
            } catch (\Throwable $e) {
                DB::rollBack();
                Notification::make()
                    ->title('Erreur lors de la mise à jour des paramètres système.')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }
        }
    }