#!/usr/bin/env python3
"""Build Filament-branded docs: home + SPA guide (hash nav, no reload)."""
from pathlib import Path
import json
import re

ROOT = Path(__file__).resolve().parent
GUIDE = ROOT / "guide"

COVER_URL = "https://github.com/ardavanshamroshan/filament-file-explorer/raw/main/docs/images/cover.png"

CORNER = '''<svg class="h-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 11 11" fill="none"><path d="M9.5 0.5H0.5V9.5" class="stroke-current" stroke="currentColor" stroke-linecap="round"></path></svg>'''

STAR = '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" fill="none" aria-hidden="true">
<path d="M8.08887 0.989258C8.31973 0.427753 9.07211 0.326095 9.44336 0.806641C9.4865 0.862481 9.52201 0.923989 9.54883 0.989258V0.991211L11.3418 5.33105L11.458 5.61328L11.7637 5.63867L16.4102 6.0127V6.01367C17.0215 6.06538 17.3475 6.75913 16.9971 7.2627C16.9591 7.31722 16.9145 7.36673 16.8643 7.41016L15.5156 8.57422L15.5205 8.57324L13.3232 10.4697L13.0938 10.668L13.1631 10.9629L14.2432 15.5381L14.2441 15.54C14.3864 16.1331 13.8328 16.6577 13.248 16.4844C13.1823 16.4648 13.1191 16.4371 13.0605 16.4014H13.0615L9.08301 13.9531L8.82031 13.792L8.55859 13.9531L4.5791 16.4004C4.05796 16.7169 3.38971 16.3508 3.37598 15.7412C3.37445 15.6735 3.38178 15.6059 3.39746 15.54V15.5391L4.48145 10.9629L4.55176 10.668L4.32129 10.4697L0.776367 7.41016C0.312197 7.00899 0.456622 6.25511 1.03613 6.05371C1.09645 6.03279 1.1591 6.01868 1.22266 6.0127L1.22363 6.01367L5.87305 5.63867L6.17773 5.61328L6.29492 5.33105L8.08789 0.991211L8.08887 0.989258Z" fill="currentColor" stroke="#292524" stroke-width="1"></path>
</svg>'''

ROCKET_SVG = '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 25 25" fill="none" aria-hidden="true">
<path class="fill-fade" d="M17.627 11.7676V17.4118C17.6269 17.6058 17.5498 17.7918 17.4127 17.9291L14.2679 21.0739C14.1717 21.17 14.0507 21.2372 13.9183 21.2684C13.786 21.2995 13.6476 21.2932 13.5186 21.2502C13.3897 21.2071 13.2753 21.129 13.1882 21.0247C13.1012 20.9203 13.0448 20.7938 13.0255 20.6592L12.5 16.8945L17.627 11.7676ZM13.2324 7.37305H7.58822C7.39423 7.37314 7.20819 7.45018 7.07095 7.58728L3.92611 10.7321C3.83009 10.8283 3.76275 10.9494 3.73163 11.0818C3.70049 11.2141 3.70681 11.3524 3.74984 11.4814C3.79289 11.6103 3.87097 11.7247 3.97535 11.8118C4.07973 11.899 4.20628 11.9553 4.34084 11.9745L8.10549 12.5L13.2324 7.37305ZM4.44338 20.5566C7.89034 20.5566 9.08419 18.754 9.4385 17.9767L7.02334 15.5615C6.24606 15.9158 4.44338 17.1097 4.44338 20.5566Z"></path>
<path class="fill-rocket" d="M21.2754 5.09522C21.2537 4.73811 21.1021 4.40127 20.849 4.14828C20.5961 3.89529 20.2593 3.74364 19.9021 3.72193C18.7504 3.65327 15.807 3.75856 13.3643 6.20027L12.9295 6.64064H7.58918C7.39608 6.63955 7.20469 6.67686 7.02614 6.75042C6.84759 6.82398 6.68545 6.9323 6.54914 7.0691L3.40888 10.2112C3.2163 10.4036 3.08117 10.646 3.01864 10.9109C2.95612 11.176 2.96868 11.4531 3.05492 11.7114C3.14116 11.9695 3.29766 12.1987 3.50686 12.3729C3.71607 12.5472 3.96969 12.6597 4.23927 12.6978L7.7613 13.1894L11.8088 17.2369L12.3005 20.7608C12.3383 21.0304 12.4507 21.284 12.6251 21.493C12.7995 21.7021 13.029 21.8582 13.2874 21.9437C13.4379 21.9939 13.5956 22.0197 13.7543 22.0197C13.9465 22.02 14.1368 21.9824 14.3145 21.9088C14.492 21.8353 14.6532 21.7273 14.7889 21.5912L17.931 18.4509C18.0678 18.3146 18.1761 18.1525 18.2497 17.9739C18.3232 17.7954 18.3605 17.604 18.3594 17.4109V12.0706L18.7962 11.6339C21.2388 9.19129 21.3441 6.24787 21.2754 5.09522Z"></path>
</svg>'''

METEOR = '''<svg xmlns="http://www.w3.org/2000/svg" width="18" height="17" viewBox="0 0 18 17" fill="none"><path d="M17.4801 0L0.610057 15.82C0.610057 15.82 0.280057 16.27 0.700057 16.71C1.11006 17.15 1.65006 16.77 1.65006 16.77L17.4801 0Z"></path></svg>'''

ARROW = '''<svg xmlns="http://www.w3.org/2000/svg" data-arrow="data-arrow" class="h-3.25 shrink-0" aria-hidden="true" viewBox="0 0 28 22" fill="none">
<path class="fill-current" fill="currentColor" d="M1 10H5.96046e-08V12H1V10ZM27 12C27.5523 12 28 11.5523 28 11C28 10.4477 27.5523 10 27 10V12ZM18 1V5.96046e-08H16V1H18ZM26.4207 11.7774C26.9055 12.0419 27.5129 11.8632 27.7774 11.3783C28.0419 10.8935 27.8632 10.286 27.3783 10.0216L26.4207 11.7774ZM15.9999 20.8995V21.8995H17.9999V20.8995H15.9999ZM1 12H26.8994V10H1V12ZM26.8994 12H27V10H26.8994V12ZM16 1C16 2.47241 16.7953 3.87873 17.7716 5.0769C18.7678 6.29956 20.0716 7.44977 21.3383 8.42854C22.6109 9.41186 23.8784 10.2469 24.825 10.835C25.2993 11.1295 25.6952 11.3635 25.9738 11.5245C26.1131 11.605 26.2233 11.6674 26.2993 11.71C26.3374 11.7314 26.3669 11.7478 26.3873 11.7591C26.3975 11.7647 26.4055 11.7691 26.411 11.7721C26.4138 11.7737 26.416 11.7749 26.4176 11.7758C26.4184 11.7762 26.4191 11.7765 26.4196 11.7768C26.4199 11.777 26.4201 11.7771 26.4202 11.7772C26.4205 11.7773 26.4207 11.7774 26.8995 10.8995C27.3783 10.0216 27.3784 10.0217 27.3785 10.0217C27.3785 10.0217 27.3785 10.0217 27.3785 10.0217C27.3784 10.0216 27.3781 10.0215 27.3777 10.0213C27.3769 10.0208 27.3756 10.0201 27.3736 10.019C27.3697 10.0168 27.3634 10.0134 27.3549 10.0087C27.3378 9.99926 27.3118 9.98479 27.2773 9.96547C27.2084 9.92682 27.1058 9.86878 26.9745 9.79288C26.7117 9.64102 26.3342 9.41799 25.8804 9.13606C24.9708 8.57104 23.7635 7.77501 22.5612 6.84596C21.353 5.91235 20.182 4.86894 19.322 3.81356C18.4422 2.73371 18 1.77759 18 1H16ZM26.8994 11C26.5248 10.0728 26.5245 10.0729 26.5242 10.0731C26.524 10.0731 26.5237 10.0733 26.5234 10.0734C26.5228 10.0736 26.522 10.0739 26.5211 10.0743C26.5193 10.0751 26.5169 10.076 26.5138 10.0773C26.5078 10.0797 26.4994 10.0832 26.4888 10.0876C26.4674 10.0964 26.4369 10.1091 26.3979 10.1257C26.3199 10.1587 26.2077 10.2071 26.0662 10.2703C25.7834 10.3967 25.3826 10.5825 24.903 10.824C23.9463 11.3055 22.6639 12.0142 21.3751 12.919C20.0914 13.8201 18.7665 14.94 17.7546 16.2535C16.7415 17.5685 15.9999 19.1342 15.9999 20.8995H17.9999C17.9999 19.715 18.4958 18.5685 19.3389 17.4742C20.1831 16.3784 21.333 15.3922 22.5242 14.5559C23.7103 13.7232 24.9028 13.0632 25.8022 12.6104C26.2507 12.3846 26.6233 12.2119 26.8818 12.0965C27.011 12.0388 27.1115 11.9955 27.1785 11.967C27.212 11.9528 27.2371 11.9424 27.2533 11.9357C27.2613 11.9324 27.2671 11.93 27.2706 11.9286C27.2724 11.9279 27.2735 11.9274 27.2741 11.9271C27.2744 11.927 27.2745 11.927 27.2745 11.927C27.2745 11.927 27.2744 11.927 27.2744 11.927C27.2742 11.9271 27.274 11.9272 26.8994 11Z"></path>
</svg>'''

CHEVRON = '''<svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>'''

GH_ICON = '''<svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0 0 16 8c0-4.42-3.58-8-8-8z"/></svg>'''

WEB_ICON = '''<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>'''

PAGES = [
    ("overview", "Overview"),
    ("installation", "Installation"),
    ("explorer", "Explorer"),
    ("files-table", "Files table"),
    ("form-picker", "Form picker"),
    ("authorization", "Authorization"),
    ("helpers", "Helpers & usage"),
    ("configuration", "Configuration"),
]

GUIDE_LEADS = {
    "overview": "What you get: Finder UI, table, picker, authorizer, generators.",
    "installation": "Composer, Media Library, theme source, plugin registration.",
    "explorer": "Grid/list, toolbar, context menu, Get Info, clipboard.",
    "files-table": "Flat Filament table over the file-explorer collection.",
    "form-picker": "Modal picker to attach explorer media into forms.",
    "authorization": "Gate browse / upload / rename / delete with a small contract.",
    "helpers": "Trait helpers, Livewire props, facade, CLI generators.",
    "configuration": "Publish config for collection, disk, upload rules, UI.",
}


def colorize(code: str, lang: str) -> str:
    import html as _html
    esc = _html.escape(code)
    if lang in ("bash", "shell"):
        lines = []
        for line in esc.split("\n"):
            line = re.sub(r"^(\s*)(composer|php|npm|npx)(\b)", r'\1<span class="tok-cmd">\2</span>\3', line)
            line = re.sub(r"(--[a-zA-Z][\w-]*)", r'<span class="tok-flag">\1</span>', line)
            line = re.sub(r"(&quot;.*?&quot;)", r'<span class="tok-str">\1</span>', line)
            lines.append(line)
        return "\n".join(lines)
    if lang in ("php", "css", "blade"):
        esc = re.sub(r"(?m)(//.*?$)", r'<span class="tok-cmt">\1</span>', esc)
        esc = re.sub(
            r"\b(use|return|function|public|final|class|implements|null|bool|true|false|new|static)\b",
            r'<span class="tok-kw">\1</span>',
            esc,
        )
        esc = re.sub(r"(&#x27;.*?&#x27;|&quot;.*?&quot;)", r'<span class="tok-str">\1</span>', esc)
    return esc


def code_panel(title: str, code: str, lang: str = "bash") -> str:
    return f'''<div class="code-panel">
  <header>
    <span class="dots" aria-hidden="true"><i></i><i></i><i></i></span>
    <span>{title}</span>
    <span class="lang">{lang}</span>
  </header>
  <pre><code class="language-{lang}">{colorize(code, lang)}</code></pre>
</div>'''


def shot(src: str, caption: str, tag: str) -> str:
    return f'''<figure class="shot">
  <img src="{src}" alt="{caption}" loading="lazy" decoding="async">
  <figcaption><span>{caption}</span><span class="tag">{tag}</span></figcaption>
</figure>'''


def honey_btn(href: str, label: str, *, rocket: bool = False) -> str:
    """Rocket animation only when rocket=True (home page)."""
    if not rocket:
        return f'''<a class="btn-plain" href="{href}">{label} {CHEVRON}</a>'''
    return f'''<div data-btn-honey class="btn-honey-wrap">
  <a href="{href}" class="btn-honey" aria-label="{label}">
    <div data-horizon-glow aria-hidden="true"></div>
    <span data-expanding-bg aria-hidden="true"></span>
    <span data-text>{label}</span>
    <span data-rocket-container>
      <span class="rocket-stage" aria-hidden="true">
        <div data-meteor style="top:-2.2rem">{METEOR}</div>
        <div data-meteor class="opacity-50" style="top:-1.7rem;transform:scale(.75)">{METEOR}</div>
        <div data-meteor style="top:0">{METEOR}</div>
        <div data-meteor class="opacity-50" style="top:.75rem;transform:scale(.75)">{METEOR}</div>
        <div data-rocket-bob>{ROCKET_SVG}</div>
      </span>
    </span>
  </a>
</div>'''


def ghost_btn(href: str, label: str) -> str:
    return f'''<div data-btn-ghost class="btn-ghost-wrap">
  <a href="{href}" class="btn-ghost" aria-label="{label}" target="_blank" rel="noopener">
    <span data-swap-icon>{GH_ICON}</span>
    <span data-text>{label}</span>
    {ARROW}
  </a>
</div>'''


def plain_btn(href: str, label: str) -> str:
    return f'''<a class="btn-plain" href="{href}">{label} {CHEVRON}</a>'''


def guide_bodies() -> dict[str, str]:
    img = "../images"
    return {
        "overview": f"""
<p>Filament File Explorer adds a Finder-style file manager to your Filament panel, backed by Spatie Media Library and scoped per owner record.</p>
{shot(f'{img}/ui-explorer-grid.png', 'Explorer grid', 'UI')}
{shot(COVER_URL, 'Package cover', 'Cover')}
<h2>Surfaces</h2>
<ul>
  <li><strong>Explorer page</strong> — sidebar tree, grid/list, toolbar, context menu, Get Info, clipboard</li>
  <li><strong>Files table</strong> — flat Filament table for power users</li>
  <li><strong>Form picker</strong> — attach explorer media into forms</li>
  <li><strong>Authorizer</strong> — gate every action with a small contract</li>
</ul>
<h2>Requirements</h2>
<ul>
  <li>PHP 8.2+</li>
  <li>Laravel 11–13</li>
  <li>Filament 4 or 5</li>
  <li>Spatie Media Library</li>
</ul>
""",
        "installation": f"""
<p>Install the package, run Media Library migrations, then register the Filament plugin.</p>
{code_panel("terminal", '''composer require ardavan/filament-file-explorer:"^0.5" -W

php artisan vendor:publish --provider="Spatie\\\\MediaLibrary\\\\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
php artisan filament-file-explorer:install --migrate
php artisan vendor:publish --tag=filament-file-explorer-assets --force''', "bash")}

<h2>Panel registration</h2>
{code_panel("AdminPanelProvider.php", '''use Ardavan\\\\FilamentFileExplorer\\\\FilamentFileExplorerPlugin;

public function panel(Panel $panel): Panel
{{
    return $panel
        ->viteTheme('resources/css/filament/admin/theme.css')
        ->plugin(FilamentFileExplorerPlugin::make());
}}''', "php")}

<h2>Theme (required)</h2>
<p>Explorer Blade views use Tailwind utilities. Source the package views and rebuild.</p>
{code_panel("resources/css/filament/admin/theme.css", '''@import '../../../../vendor/filament/filament/resources/css/theme.css';

@source '../../../../app/Filament/**/*';
@source '../../../../resources/views/filament/**/*';
@source '../../../../vendor/ardavan/filament-file-explorer/resources/views/**/*.blade.php';''', "css")}
{code_panel("build", '''npm run build
php artisan filament:assets''', "bash")}

{shot(COVER_URL, 'Theme + assets ready', 'Cover')}

<h2>Fast path</h2>
<ol>
  <li>Model: <code>use HasFileExplorer</code></li>
  <li><code>php artisan filament-file-explorer:make-folder-migration {{table}}</code> → migrate</li>
  <li><code>php artisan filament-file-explorer:make-page {{Resource}}</code></li>
  <li>Optional: <code>php artisan filament-file-explorer:make-authorizer</code></li>
</ol>

<h2>Stubs</h2>
<p>Generators use package stubs by default. Publish only when you want to customize output:</p>
{code_panel("stubs", '''php artisan vendor:publish --tag=filament-file-explorer-stubs
# or
php artisan filament-file-explorer:install --stubs''', "bash")}
""",
        "explorer": f"""
<p>Full Finder-style surface: sidebar tree, grid/list, toolbar, clipboard, Get Info, context menu.</p>
{shot(f'{img}/ui-explorer-grid.png', 'Grid view', 'MIME')}
{shot(f'{img}/ui-explorer-list.png', 'List view', 'Rows')}
{shot(f'{img}/ui-toolbar.png', 'Toolbar', 'Chrome')}
{shot(f'{img}/ui-context-menu.png', 'Context menu', 'Actions')}
{shot(f'{img}/ui-get-info.png', 'Get Info', 'Inspector')}

<h2>Embed Livewire</h2>
{code_panel("blade", '''@livewire('filament-file-explorer::file-explorer', [
    'scopeKey' => $record->fileExplorerScopeKey(),
    'rootFolderId' => $record->fileExplorerRootFolderId(),
], key('fe-'.$record->getKey()))''', "php")}

<h2>Resource pages</h2>
<p>Generate and register pages via the resource concern:</p>
{code_panel("Resource", '''use Ardavan\\\\FilamentFileExplorer\\\\Resources\\\\Concerns\\\\HasFileExplorerResource;

public static function getPages(): array
{{
    return [
        // ...
        ...static::getFileExplorerPages(
            Pages\\\\ManageProjectFiles::class,
            Pages\\\\ListProjectFiles::class,
        ),
    ];
}}''', "php")}
{code_panel("CLI", "php artisan filament-file-explorer:make-page ProjectResource", "bash")}
""",
        "files-table": f"""
<p>Flat Filament table of media in the <code>file-explorer</code> collection — filters, bulk actions, downloads.</p>
{shot(f'{img}/ui-files-table.png', 'Files table', 'Filament')}

<h2>When to use</h2>
<ul>
  <li>Operators who prefer rows over icons</li>
  <li>Bulk download / delete across folders</li>
  <li>Quick search by filename / MIME</li>
</ul>

<h2>Generated page</h2>
<p><code>make-page</code> can emit a list page that uses <code>InteractsWithFileExplorerTable</code>. Implement:</p>
<ul>
  <li><code>fileExplorerScopeKey()</code></li>
  <li><code>fileExplorerRootFolderId()</code></li>
  <li><code>fileExplorerUrl()</code> (link back to Finder UI)</li>
</ul>
""",
        "form-picker": f"""
<p>Modal picker to attach existing media from the explorer into a form field.</p>
{shot(f'{img}/ui-form-picker.png', 'Picker modal', 'Form')}

<h2>Field usage</h2>
{code_panel("Form", '''use Ardavan\\\\FilamentFileExplorer\\\\Forms\\\\Components\\\\FileExplorerPicker;

FileExplorerPicker::make('attachment_ids')
    ->scopeKey($record->fileExplorerScopeKey())
    ->rootFolderId($record->fileExplorerRootFolderId())
    ->multiple();''', "php")}
""",
        "authorization": f"""
<p>Implement <code>FileExplorerAuthorizer</code> (or generate one) to gate browse / upload / rename / delete per owner model.</p>
{code_panel("Authorizer.php", '''final class ProjectFileExplorerAuthorizer implements FileExplorerAuthorizer
{{
    public function canBrowse(Model $owner, ?Authenticatable $user = null): bool
    {{
        return $user?->can('view', $owner) ?? false;
    }}
    // canUpload, canRename, canDelete, canMkdir, canMove, canCopy, ...
}}''', "php")}

<h2>Wire it up</h2>
{code_panel("plugin", '''->plugin(
    FilamentFileExplorerPlugin::make()
        ->authorizer(ProjectFileExplorerAuthorizer::class)
)''', "php")}
{code_panel("or config", "'authorizer' => \\\\App\\\\Support\\\\ProjectFileExplorerAuthorizer::class,", "php")}
{code_panel("CLI", "php artisan filament-file-explorer:make-authorizer ProjectFileExplorerAuthorizer", "bash")}

<p>Default is <code>AllowAllAuthorizer</code> — fine for local demos, not production.</p>
""",
        "helpers": f"""
<p>Helpers keep scope + root folder consistent across explorer, table, and picker.</p>

<h2>HasFileExplorer (model)</h2>
{code_panel("Model", '''use Ardavan\\\\FilamentFileExplorer\\\\Models\\\\Concerns\\\\HasFileExplorer;

class Project extends Model
{{
    use HasFileExplorer;
}}''', "php")}
<ul>
  <li><code>fileExplorerScopeKey()</code> — unique scope string (e.g. <code>project.42</code>)</li>
  <li><code>fileExplorerRootFolderId()</code> — root folder id for the record</li>
  <li><code>ensureFileExplorerRoot()</code> — create root folder if missing</li>
  <li><code>folder()</code> — relation to package <code>Folder</code> model</li>
</ul>

<h2>Facade</h2>
{code_panel("Facade", '''use Ardavan\\\\FilamentFileExplorer\\\\Facades\\\\FileExplorer;

FileExplorer::createRoot('Project Files', 'project-files');
FileExplorer::collection(); // media collection name''', "php")}

<h2>CLI generators</h2>
{code_panel("CLI", '''php artisan filament-file-explorer:make-folder-migration projects
php artisan filament-file-explorer:make-page ProjectResource
php artisan filament-file-explorer:make-authorizer ProjectFileExplorerAuthorizer''', "bash")}

<h2>Open explorer action</h2>
<p><code>HasFileExplorerResource::openFileExplorerAction()</code> adds a Filament action that jumps to the Finder page for the record.</p>

<h2>Usage checklist</h2>
<ol>
  <li>Trait on owner model + folder migration</li>
  <li>Authorizer bound (plugin or config)</li>
  <li>Theme <code>@source</code> + assets published</li>
  <li>Register explorer / list pages on the resource</li>
  <li>Optional: form picker fields with matching <code>scopeKey</code> / <code>rootFolderId</code></li>
</ol>
""",
        "configuration": f"""
<p>Publish config to tune collection name, disk, upload rules, and UI defaults.</p>
{code_panel("publish", "php artisan vendor:publish --tag=filament-file-explorer-config", "bash")}

<h2>Useful keys</h2>
<ul>
  <li><code>authorizer</code> — class binding</li>
  <li><code>collection</code> — media collection (<code>file-explorer</code>)</li>
  <li><code>auto_create_root</code> — bool</li>
  <li><code>upload.max_size_kb</code> / <code>allowed_extensions</code> / <code>allowed_mime_types</code></li>
  <li><code>folders.max_depth</code> / <code>folders.table</code></li>
  <li><code>routes.prefix</code> / <code>middleware</code></li>
</ul>
""",
    }


def nav_link(href: str, label: str, active: bool = False) -> str:
    cls = "nav-link is-active" if active else "nav-link"
    return f'''<a class="{cls}" href="{href}">
  <span class="corner corner-tl">{CORNER}</span>
  <span>{label}</span>
  <span class="corner corner-br">{CORNER}</span>
</a>'''


def header(prefix: str, active: str | None = None) -> str:
    home = f"{prefix}index.html" if prefix else "./"
    # Home → guide/; inside guide SPA → index.html
    docs = "index.html" if prefix else "guide/"
    features = "#features" if not prefix else f"{prefix}index.html#features"
    return f'''
<header class="site-header">
  <div class="wrap header-bar">
    <div class="header-left">
      <a class="brand-cell" href="{home}" aria-label="Filament File Explorer home">
        <span class="plugin-mark"><img src="{prefix}images/logo.png" alt="" width="32" height="32" decoding="async"></span>
        <span class="plugin-title">File Explorer<small>for Filament</small></span>
      </a>
      <a class="filament-logo-link" href="https://filamentphp.com" target="_blank" rel="noopener noreferrer">Filament</a>
      <nav class="nav-desktop" aria-label="Primary">
        {nav_link(home if not prefix else f"{prefix}", "Home", active == "home")}
        {nav_link(features, "Features")}
        {nav_link(docs, "Docs", active == "docs")}
        {nav_link("https://github.com/ardavanshamroshan/filament-file-explorer", "GitHub")}
      </nav>
    </div>
    <div class="header-right">
      <div class="header-actions">
        <a class="gh-stars" href="https://github.com/ardavanshamroshan/filament-file-explorer" target="_blank" rel="noopener noreferrer" aria-label="Star on GitHub">
          <div class="star-wrap">{STAR}</div>
          <span>Star</span>
        </a>
        <a class="docs-cta-link" href="{docs}">Docs</a>
      </div>
      <button type="button" class="menu-toggle" data-menu-open aria-label="Open menu">Menu</button>
    </div>
  </div>
  <div class="mobile-overlay" data-mobile-menu data-menu-close hidden></div>
  <div class="mobile-panel" data-mobile-menu hidden>
    <div class="mobile-handle"></div>
    <a href="{home}" data-menu-close>Home</a>
    <a href="{docs}" data-menu-close>Documentation</a>
    <a href="https://github.com/ardavanshamroshan/filament-file-explorer" data-menu-close>GitHub</a>
    <a href="https://ardavanshamroshan.ir" data-menu-close>ardavanshamroshan.ir</a>
    <a href="#" data-menu-close style="margin-top:.5rem;font-weight:600">Close</a>
  </div>
</header>
'''


def footer(prefix: str) -> str:
    home = "./" if not prefix else f"{prefix}"
    docs = "guide/" if not prefix else "index.html"
    return f'''
<footer class="site-footer">
  <div class="wrap footer-row">
    <div>MIT © <a href="https://ardavanshamroshan.ir" style="font-weight:700;color:var(--color-stone-800)">Ardavan Shamroshan</a></div>
    <div class="footer-links">
      <a href="{home}">Home</a>
      <a href="{docs}">Docs</a>
      <a href="https://github.com/ardavanshamroshan/filament-file-explorer">GitHub</a>
      <a href="https://filamentphp.com" target="_blank" rel="noopener">Filament</a>
      <a href="https://ardavanshamroshan.ir">ardavanshamroshan.ir</a>
    </div>
  </div>
</footer>
'''


def author_block(prefix: str) -> str:
    return f'''
<section class="section" id="author">
  <div class="wrap">
    <div class="section-head">
      <div>
        <h2>Maintainer</h2>
        <p>Built &amp; maintained by Ardavan Shamroshan.</p>
      </div>
    </div>
    <div class="author">
      <img src="{prefix}images/author.png" alt="Ardavan Shamroshan" width="88" height="88" loading="lazy" decoding="async">
      <div>
        <h3>Ardavan Shamroshan</h3>
        <p>Laravel · Filament · product-minded admin UX. Finder-style explorer for panels that need real file workflows.</p>
        <div class="author-links">
          <a class="chip" href="mailto:shamroshanardavan@gmail.com">shamroshanardavan@gmail.com</a>
          <a class="chip" href="https://ardavanshamroshan.ir" target="_blank" rel="noopener">{WEB_ICON} ardavanshamroshan.ir</a>
          <a class="chip" href="https://github.com/ardavanshamroshan" target="_blank" rel="noopener">{GH_ICON} @ardavanshamroshan</a>
          <a class="chip" href="https://github.com/ardavanshamroshan/filament-file-explorer" target="_blank" rel="noopener">{GH_ICON} Plugin repo</a>
          <a class="chip" href="https://filamentphp.com" target="_blank" rel="noopener">FilamentPHP</a>
        </div>
      </div>
    </div>
  </div>
</section>
'''


def minify_css(s: str) -> str:
    s = re.sub(r"/\*.*?\*/", "", s, flags=re.S)
    s = re.sub(r"\s+", " ", s)
    s = re.sub(r"\s*([{}:;,>~+])\s*", r"\1", s)
    return s.strip()


def minify_html(s: str) -> str:
    s = re.sub(r">\s+<", "><", s)
    s = re.sub(r"\n\s*", "\n", s)
    return s.strip() + "\n"


def shell(title: str, description: str, body: str, prefix: str = "", active: str | None = None, og_image: str = "images/cover.png") -> str:
    css_href = f"{prefix}assets/site.min.css"
    js_href = f"{prefix}assets/site.min.js"
    icon = f"{prefix}images/logo.png"
    fonts = "https://fonts.googleapis.com/css2?family=Albert+Sans:wght@500;700&family=Roboto+Mono:wght@400;500&display=swap"
    html = f'''<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{title}</title>
<meta name="description" content="{description}">
<meta property="og:title" content="{title}">
<meta property="og:description" content="{description}">
<meta property="og:image" content="https://ardavanshamroshan.github.io/filament-file-explorer/{og_image}">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="{icon}" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="{fonts}" rel="stylesheet">
<link rel="stylesheet" href="{css_href}">
<script defer src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script defer src="{js_href}"></script>
</head>
<body>
{header(prefix, active)}
{body}
{author_block(prefix) if not prefix else ""}
{footer(prefix)}
</body>
</html>'''
    return minify_html(html)


def build_index():
    body = f'''
<main>
  <section class="hero">
    <div class="wrap hero-grid">
      <div>
        <div class="eyebrow">
          <img src="images/logo.png" alt="" width="16" height="16" style="width:1rem;height:1rem;border-radius:.25rem">
          Filament v4 · v5 · Spatie Media Library
        </div>
        <h1>Finder-style files for your Filament panel</h1>
        <p class="lead">Sidebar tree, MIME icons, drag-and-drop upload, clipboard, Get Info, and a files table — scoped per record with a clean authorizer contract.</p>
        <div class="hero-actions cta-row">
          {honey_btn("guide/#installation", "Get started", rocket=True)}
          {ghost_btn("https://github.com/ardavanshamroshan/filament-file-explorer", "View on GitHub")}
        </div>
        <div class="hero-meta">
          <span><strong>PHP</strong> 8.2+</span>
          <span><strong>Laravel</strong> 11–13</span>
          <span><strong>Filament</strong> 4 &amp; 5</span>
          <span><strong>MIT</strong> license</span>
        </div>
      </div>
      <div class="hero-art">
        <img src="{COVER_URL}" alt="Filament File Explorer cover" width="1536" height="1024" fetchpriority="high" decoding="async" referrerpolicy="no-referrer">
      </div>
    </div>
  </section>

  <section class="section" id="features">
    <div class="wrap">
      <div class="section-head">
        <div>
          <h2>Built for real admin workflows</h2>
          <p>Desktop file manager muscle — Livewire panel manners.</p>
        </div>
        {plain_btn("guide/#explorer", "Explorer guide")}
      </div>
      <div class="cards">
        <article class="card"><div class="card-icon">⌘</div><h3>Finder UI</h3><p>Sidebar tree, breadcrumbs, back/forward, responsive toolbar with overflow ⋮ menu.</p></article>
        <article class="card"><div class="card-icon">↑</div><h3>Upload &amp; MIME</h3><p>Drag-and-drop, typed icons (PDF, Office, zip, audio, video), extensions on labels.</p></article>
        <article class="card"><div class="card-icon">⧉</div><h3>Clipboard</h3><p>Copy, cut, paste across folders — scoped to the owner model.</p></article>
        <article class="card"><div class="card-icon">ℹ</div><h3>Get Info</h3><p>Inspector panel for size, MIME, timestamps, and labels.</p></article>
        <article class="card"><div class="card-icon">☰</div><h3>Files table</h3><p>Flat Filament table page for power users who prefer rows over icons.</p></article>
        <article class="card"><div class="card-icon">🛡</div><h3>Authorizer</h3><p>Gate every action with a small contract — no global filesystem free-for-all.</p></article>
      </div>
    </div>
  </section>

  <section class="section" id="screenshots">
    <div class="wrap">
      <div class="section-head">
        <div>
          <h2>Screenshots</h2>
          <p>Grid, list, toolbar, menus, Get Info, table, picker.</p>
        </div>
      </div>
      <div class="bento">
        <figure class="shot shot-lg">
          <img src="images/ui-explorer-grid.png" loading="lazy" decoding="async" alt="Grid view">
          <figcaption><span>Grid view</span><span class="tag">Finder</span></figcaption>
        </figure>
        <figure class="shot shot-md">
          <img src="images/ui-explorer-list.png" loading="lazy" decoding="async" alt="List view">
          <figcaption><span>List view</span><span class="tag">Rows</span></figcaption>
        </figure>
        <figure class="shot shot-md">
          <img src="images/ui-toolbar.png" loading="lazy" decoding="async" alt="Toolbar">
          <figcaption><span>Toolbar</span><span class="tag">Chrome</span></figcaption>
        </figure>
        <figure class="shot shot-sm">
          <img src="images/ui-context-menu.png" loading="lazy" decoding="async" alt="Context menu">
          <figcaption><span>Menus</span><span class="tag">Actions</span></figcaption>
        </figure>
        <figure class="shot shot-wide">
          <img src="images/ui-get-info.png" loading="lazy" decoding="async" alt="Get Info">
          <figcaption><span>Get Info</span><span class="tag">Inspector</span></figcaption>
        </figure>
        <figure class="shot shot-md">
          <img src="images/ui-files-table.png" loading="lazy" decoding="async" alt="Files table">
          <figcaption><span>Files table</span><span class="tag">Filament</span></figcaption>
        </figure>
        <figure class="shot shot-md">
          <img src="images/ui-form-picker.png" loading="lazy" decoding="async" alt="Form picker">
          <figcaption><span>Form picker</span><span class="tag">Modal</span></figcaption>
        </figure>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="wrap">
      <div class="section-head">
        <div>
          <h2>Install in minutes</h2>
          <p>Composer → migrate → theme → plugin.</p>
        </div>
      </div>
      {code_panel("terminal", '''composer require ardavan/filament-file-explorer:"^0.5" -W
php artisan filament-file-explorer:install --migrate
php artisan vendor:publish --tag=filament-file-explorer-assets --force''', "bash")}
      <div class="cta-band" style="margin-top:1.5rem">
        <div>
          <h2>Ready for your panel?</h2>
          <p>SPA docs: install, explorer, helpers, authorizers — no reload.</p>
        </div>
        <div class="cta-row">
          {honey_btn("guide/#installation", "Get started", rocket=True)}
          {ghost_btn("https://packagist.org/packages/ardavan/filament-file-explorer", "Packagist")}
        </div>
      </div>
    </div>
  </section>
</main>
'''
    (ROOT / "index.html").write_text(
        shell(
            "Filament File Explorer — Finder-style files for Filament",
            "Finder-style file explorer plugin for Filament v4 and v5, powered by Spatie Media Library.",
            body,
            prefix="",
            active="home",
        ),
        encoding="utf-8",
    )


def build_guide_spa():
    bodies = guide_bodies()
    titles = {slug: label for slug, label in PAGES}
    titles_json = json.dumps(titles)
    leads_json = json.dumps(GUIDE_LEADS)

    nav = "\n".join(
        f'<a href="#{slug}" data-doc-link="{slug}">{label}</a>'
        for slug, label in PAGES
    )
    panels = "\n".join(
        f'<section class="doc-panel" data-doc-panel="{slug}" hidden>\n{bodies[slug]}\n</section>'
        for slug, _ in PAGES
    )

    body = f'''
<main>
  <section class="docs-hero">
    <div class="wrap docs-hero-grid">
      <div>
        <div class="eyebrow">
          <img src="../images/logo.png" alt="" width="16" height="16" style="width:1rem;height:1rem;border-radius:.25rem">
          Documentation
        </div>
        <h1 data-doc-title>Overview</h1>
        <p class="lead" data-doc-lead>{GUIDE_LEADS["overview"]}</p>
        <div class="hero-actions cta-row">
          {ghost_btn("https://github.com/ardavanshamroshan/filament-file-explorer", "GitHub")}
        </div>
      </div>
      <div class="hero-art">
        <img src="{COVER_URL}" alt="Filament File Explorer cover" width="1536" height="1024" loading="lazy" decoding="async" referrerpolicy="no-referrer">
      </div>
    </div>
  </section>
  <div class="wrap docs-shell" data-docs-spa data-titles='{titles_json}' data-leads='{leads_json}'>
    <aside class="docs-side">
      <h4>Documentation</h4>
      <nav aria-label="Guide pages">{nav}</nav>
      <h4 style="margin-top:1rem">Resources</h4>
      <nav>
        <a href="../">Home</a>
        <a href="https://github.com/ardavanshamroshan/filament-file-explorer">Repository</a>
        <a href="https://packagist.org/packages/ardavan/filament-file-explorer">Packagist</a>
        <a href="https://ardavanshamroshan.ir">Author</a>
      </nav>
    </aside>
    <article class="docs-main">
      {panels}
    </article>
  </div>
</main>
{author_block("../")}
'''
    html = shell(
        "Docs — Filament File Explorer",
        "Usage guide for Filament File Explorer: install, explorer, helpers, authorization.",
        body,
        prefix="../",
        active="docs",
    )
    GUIDE.mkdir(exist_ok=True)
    (GUIDE / "index.html").write_text(html, encoding="utf-8")


def build_redirects():
    """Old multi-page URLs → SPA hash (keeps bookmarks working)."""
    for slug, _ in PAGES:
        (GUIDE / f"{slug}.html").write_text(
            f'''<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Redirecting…</title>
<meta http-equiv="refresh" content="0;url=index.html#{slug}">
<link rel="canonical" href="index.html#{slug}">
<script>location.replace("index.html#{slug}");</script>
</head>
<body>
<p><a href="index.html#{slug}">Continue to docs</a></p>
</body>
</html>
''',
            encoding="utf-8",
        )
    # legacy explorer-page name
    (GUIDE / "explorer-page.html").write_text(
        '''<!DOCTYPE html><html><head><meta charset="utf-8"><meta http-equiv="refresh" content="0;url=index.html#explorer"><script>location.replace("index.html#explorer");</script></head><body></body></html>\n''',
        encoding="utf-8",
    )


if __name__ == "__main__":
    css = (ROOT / "assets" / "site.css").read_text(encoding="utf-8")
    js = (ROOT / "assets" / "site.js").read_text(encoding="utf-8")
    (ROOT / "assets" / "site.min.css").write_text(minify_css(css), encoding="utf-8")
    (ROOT / "assets" / "site.min.js").write_text(js.strip() + "\n", encoding="utf-8")
    build_index()
    build_guide_spa()
    build_redirects()
    print("Built home + SPA guide + redirects")
