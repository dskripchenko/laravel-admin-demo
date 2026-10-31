<?php

namespace App\Admin\Showcase\Forms;

use App\Admin\Showcase\Layouts\DocumentSamples;
use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Field\Bpmn;
use Dskripchenko\LaravelAdmin\Field\Calendar;
use Dskripchenko\LaravelAdmin\Field\Dmn;
use Dskripchenko\LaravelAdmin\Field\Field;
use Dskripchenko\LaravelAdmin\Field\Kanban;
use Dskripchenko\LaravelAdmin\Field\MarkdownEditor;
use Dskripchenko\LaravelAdmin\Field\ValidationRulesExporter;
use Dskripchenko\LaravelAdmin\Infolist\BpmnEntry;
use Dskripchenko\LaravelAdmin\Infolist\CalendarEntry;
use Dskripchenko\LaravelAdmin\Infolist\DmnEntry;
use Dskripchenko\LaravelAdmin\Infolist\KanbanEntry;
use Dskripchenko\LaravelAdmin\Infolist\MarkdownEntry;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Forms › Editor fields: a BPMN process and DMN rules edited in the
 * modelers of @dskripchenko/bpmn, a kanban board and a calendar edited as
 * cards and events, and Markdown in a word-processor editor. Each value is
 * text — XML, markup, Markdown — in an ordinary text column; the rules the
 * fields export check it on the server. Below, the same values drawn
 * read-only by infolist entries, as a record's view page draws them.
 */
final class EditorFieldsScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-forms-editors';
    }

    public static function group(): string
    {
        return 'forms';
    }

    public static function icon(): string
    {
        return 'pen-line';
    }

    public static function badge(): ?string
    {
        return 'new';
    }

    public function name(): string
    {
        return __('Editor fields');
    }

    public function description(): ?string
    {
        return __('Bpmn, Dmn, Kanban, Calendar and MarkdownEditor fields, validated on the server, and their read-only entries.');
    }

    public function query(mixed ...$params): array
    {
        return [
            'process' => DocumentSamples::file('order.bpmn'),
            'rules' => DocumentSamples::file('invoice.dmn'),
            'board' => DocumentSamples::kanban(),
            'schedule' => DocumentSamples::calendar(),
            'notes' => __("## Release notes\n\nThe **visual** mode edits like a word processor; the *source* is plain Markdown.\n\n- Type `/` for blocks: diagrams, BPMN, kanban, calendar\n- Switch to **Split** to see both\n\n```mermaid\nflowchart LR\n  draft[Draft] --> review[Review] --> published([Published])\n```"),
        ];
    }

    /**
     * The fields: the form and its rules in one place.
     *
     * @return list<Field>
     */
    private function fields(): array
    {
        return [
            Bpmn::make('process')->title(__('Process'))->required()->height(520),
            Dmn::make('rules')->title(__('Decision rules'))->height(440),
            Kanban::make('board')->title(__('Board'))->height(460)->maxLength(20_000),
            Calendar::make('schedule')->title(__('Schedule'))->initialView('month')->views(['month', 'week', 'agenda']),
            MarkdownEditor::make('notes')->title(__('Notes'))->modes(['visual', 'source', 'split'])->minHeight(280),
        ];
    }

    protected function demo(): array
    {
        [$process, $rules, $board, $schedule, $notes] = $this->fields();

        return [
            Layout::tabs([
                'BPMN' => [Layout::rows([$process])],
                'DMN' => [Layout::rows([$rules])],
                __('Kanban') => [Layout::rows([$board])],
                __('Calendar') => [Layout::rows([$schedule])],
                'Markdown' => [Layout::rows([$notes])],
            ]),
            Layout::block(__('The same values, read-only'), [
                Layout::infolist([
                    BpmnEntry::make('process')->label(__('Process'))->height(360)->highlight(['Task_Check']),
                    DmnEntry::make('rules')->label(__('Decision rules'))->height(320),
                    KanbanEntry::make('board')->label(__('Board')),
                    CalendarEntry::make('schedule')->label(__('Schedule'))->initialView('agenda'),
                    MarkdownEntry::make('notes')->label(__('Notes')),
                ]),
            ])->icon('eye')
                ->description(__('BpmnEntry, DmnEntry, KanbanEntry, CalendarEntry and MarkdownEntry: what a view page shows. They follow the form as you edit it.')),
        ];
    }

    public function commandBar(): array
    {
        return [Button::make(__('Submit'))->method('submit')->primary()->icon('send')];
    }

    /**
     * Checks the values with the rules the fields export: `string`, the
     * BPMN / DMN model rules (safe XML, the right root and namespace, the
     * size cap) and the markup's length.
     *
     * @param  array<string, mixed>  $state
     */
    public function submit(array $state): array
    {
        $data = validator($state, ValidationRulesExporter::export($this->fields()))->validate();

        return ['message' => __('Valid! :tasks tasks in the process, :cards cards on the board, :events events in the calendar.', [
            'tasks' => preg_match_all('/<(?:\w+:)?\w*[tT]ask\b/', (string) $data['process']),
            'cards' => preg_match_all('/^\s*[-*] /m', (string) ($data['board'] ?? '')),
            'events' => preg_match_all('/^(?:\d{4}-\d{2}-\d{2}|every )/m', (string) ($data['schedule'] ?? '')),
        ])];
    }
}
