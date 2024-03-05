<?php

namespace MailCarrier\Resources;

use Carbon\CarbonInterface;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Actions\Action as TablesAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use MailCarrier\Actions\Logs\GetTriggers;
use MailCarrier\Actions\SendMail;
use MailCarrier\Dto\AttachmentDto;
use MailCarrier\Dto\LogTemplateDto;
use MailCarrier\Dto\SendMailDto;
use MailCarrier\Enums\LogStatus;
use MailCarrier\Facades\MailCarrier;
use MailCarrier\Models\Attachment;
use MailCarrier\Models\Log;
use MailCarrier\Models\Template;
use MailCarrier\Resources\LogResource\Pages;

class LogResource extends Resource
{
    protected static ?string $model = Log::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    /**
     * List all the records.
     */
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('recipient')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (LogStatus $state): string => match ($state) {
                        LogStatus::Pending => 'warning',
                        LogStatus::Failed => 'danger',
                        LogStatus::Sent => 'success',
                    })
                    ->icon(fn (LogStatus $state): string => match ($state) {
                        LogStatus::Pending => 'heroicon-o-clock',
                        LogStatus::Failed => 'heroicon-o-exclamation-triangle',
                        LogStatus::Sent => 'heroicon-o-check-circle',
                    })
                    ->formatStateUsing(fn (LogStatus $state) => ucfirst(mb_strtolower($state->value)))
                    ->tooltip(fn (Log $record) => $record->error),

                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(60)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column content exceeds the length limit.
                        return $state;
                    })
                    ->icon(fn (Log $record) => $record->attachments->isNotEmpty() ? 'heroicon-o-paper-clip' : '')
                    ->iconColor('primary'),

                Tables\Columns\TextColumn::make('tries')
                    ->badge()
                    ->tooltip(function (Log $record) {
                        if ($record->status !== LogStatus::Failed || is_null($record->last_try_at)) {
                            return null;
                        }

                        // We add "1" to retries count because the first try is not counted as "retry"
                        if ($record->tries >= count(MailCarrier::getEmailRetriesBackoff()) + 1) {
                            return 'No retry left.';
                        }

                        return 'Retrying in ' . $record->last_try_at
                            ->addSeconds(
                                MailCarrier::getEmailRetriesBackoff()[max(0, $record->tries - 1)]
                            )
                            ->diffForHumans(syntax: CarbonInterface::DIFF_ABSOLUTE);
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sent at')
                    ->since()
                    ->tooltip(fn (Log $record): string => $record->created_at->toRfc7231String()),
            ])
            ->poll(Config::get('mailcarrier.logs.table_refresh_poll', '5s'))
            ->recordAction('details')
            ->filters(static::getTableFilters())
            ->filtersTriggerAction(
                fn (TablesAction $action) => $action
                    ->button()
                    ->label('Filter')
                    ->slideOver(),
            )
            ->actions(
                Tables\Actions\ActionGroup::make(static::getTableActions())
            );
    }

    /**
     * Get Filament CRUD pages.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLogs::route('/'),
        ];
    }

    /**
     * Get the table filters.
     */
    protected static function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('template')
                ->relationship('template', 'name')
                ->searchable()
                ->preload(),

            Tables\Filters\SelectFilter::make('trigger')
                ->options((new GetTriggers())->run()),
        ];
    }

    /**
     * Get the table actions.
     */
    protected static function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('details')
                ->icon('heroicon-o-information-circle')
                ->modalContent(fn (Log $record) => View::make('mailcarrier::modals.details', [
                    'log' => $record,
                    'variables' => json_encode($record->variables, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                    'template' => static::getTemplateValue($record->template_frozen, $record->template),
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalFooterActionsAlignment(Alignment::Right),

            Tables\Actions\Action::make('preview')
                ->icon('heroicon-o-eye')
                ->modalContent(fn (Log $record) => View::make('mailcarrier::modals.preview', [
                    'id' => $record->id,
                ]))
                ->modalWidth('7xl')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalFooterActionsAlignment(Alignment::Right),

            Tables\Actions\Action::make('resend_email')
                ->label(fn (Log $record) => $record->isFailed() ? 'Retry now' : 'Send again')
                ->icon('heroicon-o-arrow-path')
                ->color(fn (Log $record) => $record->isFailed() ? Color::Orange : 'primary')
                ->form([
                    FileUpload::make('attachments')
                        ->label('Additional attachments')
                        ->helperText('Any extra attachment will be sent along the original ones')
                        ->multiple()
                        ->preserveFilenames()
                        ->storeFiles(false),
                ])
                ->modalWidth('2xl')
                ->modalIcon('heroicon-o-arrow-path')
                ->modalDescription(
                    fn (Log $record) => $record->isFailed()
                        ? 'Are you sure you want to manually retry to send this email?'
                        : 'Are you sure you want to to send again this email?'
                )
                ->modalSubmitActionLabel(fn (Log $record) => $record->isFailed() ? 'Retry' : 'Resend')
                ->modalFooterActionsAlignment(Alignment::Right)
                ->action(fn (?Log $record, array $data) => $record ? static::resendEmail($record, $data) : null)
                ->visible(fn (?Log $record) => $record?->status !== LogStatus::Pending),
        ];
    }

    /**
     * Format the template value.
     */
    protected static function getTemplateValue(LogTemplateDto $templateDto, ?Template $template): HtmlString
    {
        $label = $template?->name ?: $templateDto->name;
        $iconColor = is_null($template) ? 'text-red-500' : 'text-yellow-400';
        $icon = match (true) {
            is_null($template) => 'heroicon-o-x-circle',
            $template->getHash() !== $templateDto->hash => 'heroicon-o-exclamation-circle',
            strtolower($template->name) !== strtolower($templateDto->name) => 'heroicon-o-clock',
            default => null,
        };
        $subtitle = match (true) {
            is_null($template) => 'Template has been deleted.',
            $template->getHash() !== $templateDto->hash => 'Template has changed.',
            strtolower($template->name) !== strtolower($templateDto->name) => 'Template has been renamed.',
            default => null,
        };

        return new HtmlString(
            ($icon ? svg($icon, 'w-5 h-5 inline-block mr-1 ' . $iconColor)->toHtml() : '') .
                $label .
                ($subtitle ? '<p class="text-xs mt-1 text-slate-300">' . $subtitle . '</p>' : '')
        );
    }

    protected static function resendEmail(Log $log, array $data): void
    {
        // Update the status to Failed just to send email again
        if ($log->isSent()) {
            $log->update([
                'status' => LogStatus::Failed,
            ]);
        }

        try {
            SendMail::resolve()->run(
                new SendMailDto([
                    'template' => $log->template->slug,
                    'subject' => $log->subject,
                    'sender' => $log->sender,
                    'recipient' => $log->recipient,
                    'cc' => $log->cc->all(),
                    'bcc' => $log->bcc->all(),
                    'variables' => $log->variables,
                    'trigger' => $log->trigger,
                    'tags' => $log->tags ?: [],
                    'metadata' => $log->metadata ?: [],
                    'attachments' => $log->attachments
                        ->map(
                            fn (Attachment $attachment) => !$attachment->canBeDownloaded()
                                ? null
                                : new AttachmentDto(
                                    name: $attachment->name,
                                    content: $attachment->content,
                                    size: $attachment->size
                                )
                        )
                        ->filter()
                        ->merge(
                            Collection::make($data['attachments'])->map(
                                fn (TemporaryUploadedFile $file) => new AttachmentDto(
                                    name: $file->getClientOriginalName(),
                                    content: base64_encode(file_get_contents($file->getRealPath())),
                                    size: $file->getSize()
                                )
                            )
                        )
                        ->all(),
                ]),
                $log
            );
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error while sending email')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->icon('heroicon-o-paper-airplane')
            ->title('Email sent correctly')
            ->success()
            ->send();
    }
}
