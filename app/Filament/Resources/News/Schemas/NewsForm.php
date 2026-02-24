<?php

namespace App\Filament\Resources\News\Schemas;

use App\Enums\NewsStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class NewsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                FileUpload::make('image')
                    ->image(),
                TextInput::make('source_url')
                    ->url()
                    ->required(),
                TextInput::make('external_id')
                    ->required(),
                Textarea::make('raw_content')
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(NewsStatus::class)
                    ->default('new')
                    ->required(),
                DateTimePicker::make('published_at')
                    ->required(),
                TextInput::make('source')
                    ->required(),
                Textarea::make('ai_content')
                    ->columnSpanFull(),
            ]);
    }
}
