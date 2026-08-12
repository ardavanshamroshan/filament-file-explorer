#!/usr/bin/env python3
"""Build Filament-branded docs HTML pages."""
from pathlib import Path

ROOT = Path(__file__).resolve().parent
GUIDE = ROOT / "guide"

FILAMENT_LOGO = r'''
<svg class="h-10" viewBox="0 0 144 43" version="1.1" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <path class="logoF1 fill-cocoa" d="M7.223,12.685L7.223,11.42C7.223,9.471 7.871,8.001 9.813,8.001L11.55,8.001L11.55,4L9.813,4C5.349,4 3.033,6.769 3.033,11.42L3.033,12.685L0,12.685L0,16.035L3.033,16.035L3.033,28.651L7.223,28.651L7.223,16.035L12.348,16.035L12.348,12.685L7.223,12.685Z"></path>
  <path class="logoF2 fill-cocoa" d="M9.772,8.012L12.283,8.012C12.809,6.58 13.529,5.243 14.407,4.03L9.772,4.03C5.327,4.03 3.02,6.787 3.02,11.416L3.02,12.676L0,12.676L0,16.011L3.02,16.011L3.02,28.571L7.194,28.571L7.194,16.011L11.426,16.011C11.324,15.268 11.267,14.511 11.267,13.74C11.267,13.382 11.282,13.028 11.305,12.676L7.194,12.676L7.194,11.416C7.194,9.476 7.839,8.012 9.772,8.012" style="opacity:.35"></path>
  <rect class="logoL fill-cocoa" x="44.27" y="4.029" width="4.139" height="24.542"></rect>
  <path class="logoA fill-cocoa" d="M59.517,24.895C57.243,24.895 55.139,23.159 55.139,20.606C55.139,18.052 57.243,16.317 59.517,16.317C61.791,16.317 63.86,17.848 63.86,20.606C63.86,23.363 61.757,24.895 59.517,24.895ZM63.86,14.649C62.706,12.879 60.365,12.335 58.94,12.335C54.766,12.335 50.864,15.5 50.864,20.606C50.864,25.711 54.766,28.877 58.94,28.877C60.501,28.877 62.842,28.162 63.86,26.528L63.86,28.571L67.999,28.571L67.999,12.675L63.86,12.675L63.86,14.649Z"></path>
  <path class="logoM fill-cocoa" d="M89.815,12.335C88.56,12.335 86.32,12.709 84.827,15.331C83.945,13.39 82.282,12.505 79.772,12.369C78.278,12.369 76.106,13.084 75.225,15.024L75.225,12.676L71.085,12.676L71.085,28.572L75.225,28.572L75.225,20.096C75.225,17.374 76.853,16.386 78.55,16.386C80.247,16.386 81.434,17.611 81.468,19.824L81.468,28.571L85.608,28.571L85.608,20.096C85.608,17.679 86.965,16.385 88.832,16.385C90.528,16.385 91.818,17.645 91.818,19.925L91.818,28.571L95.924,28.571L95.924,19.483C95.924,14.819 93.65,12.335 89.816,12.335"></path>
  <path class="logoE fill-cocoa" d="M102.121,19.176C102.427,17.031 104.123,15.806 106.295,15.806C108.331,15.806 109.959,16.997 110.265,19.176L102.121,19.176ZM106.261,12.335C101.748,12.335 98.05,15.569 98.05,20.572C98.05,25.576 101.748,28.912 106.261,28.912C109.179,28.912 112.233,27.788 113.658,25.065C112.64,24.52 111.487,23.908 110.503,23.363C109.757,24.725 108.128,25.406 106.533,25.406C104.192,25.406 102.427,24.112 102.156,22.002L114.235,22.002C114.269,21.628 114.303,20.981 114.303,20.573C114.303,15.569 110.775,12.335 106.262,12.335"></path>
  <path class="logoN fill-cocoa" d="M125.97,12.335C124.137,12.335 122.135,13.322 121.253,15.023L121.253,12.675L117.113,12.675L117.113,28.571L121.253,28.571L121.253,20.096C121.253,17.338 122.848,16.385 124.714,16.385C126.579,16.385 127.734,17.61 127.734,19.925L127.734,28.571L131.874,28.571L131.874,19.176C131.874,14.682 129.668,12.334 125.97,12.334"></path>
  <path class="logoT fill-cocoa" d="M136.535,6.616L136.535,12.675L133.65,12.675L133.65,16.011L136.535,16.011L136.535,28.571L140.641,28.571L140.641,16.011L144,16.011L144,12.675L140.641,12.675L140.641,6.616L136.535,6.616Z"></path>
  <path class="logoLightBulb fill-honey-200" d="M25.233,7.782C25.233,9.213 26.455,10.303 27.915,10.303C29.375,10.303 30.53,9.213 30.53,7.782C30.53,6.352 29.341,5.296 27.915,5.296C26.489,5.296 25.233,6.386 25.233,7.782Z"></path>
  <path class="logoLightBulb fill-honey-200" d="M33.7,1.323C30.892,-0.035 27.654,-0.361 24.586,0.407C22.56,0.916 20.657,1.909 19.088,3.277C18.809,3.513 18.539,3.765 18.277,4.029C17.4,4.915 16.623,5.955 15.965,7.135C15.802,7.423 15.652,7.715 15.511,8.011C14.805,9.496 14.374,11.08 14.241,12.674C14.211,13.046 14.194,13.419 14.195,13.792C14.185,14.526 14.244,15.266 14.368,16.01C14.511,16.859 14.736,17.712 15.051,18.56C15.565,19.922 16.262,21.168 16.929,22.322C17.603,23.527 18.154,24.541 18.648,25.566C19.044,26.406 19.395,27.283 19.698,28.178C19.796,28.469 19.889,28.762 19.977,29.057L20.155,29.691C20.299,30.209 20.577,30.648 20.957,30.959C21.656,31.533 22.585,31.667 23.464,31.308L25.825,30.177L28.861,28.723L28.786,27.072L30.929,26.852L30.953,26.85L30.971,26.837C31.092,26.751 31.113,26.425 31.114,26.24C31.115,26.094 31.105,25.604 30.928,25.408L29.139,24.285L30.922,24.119L30.941,24.105C31.088,23.996 31.107,23.333 31.008,22.95C30.948,22.712 30.845,22.56 30.72,22.507L29.086,21.516C29.248,21.507 29.416,21.503 29.59,21.498C29.985,21.488 30.393,21.478 30.736,21.406C30.857,21.381 30.944,21.279 30.993,21.104C31.092,20.75 31.021,20.131 30.855,19.901C30.775,19.791 30.413,19.576 29.577,19.103C29.33,18.963 29.096,18.831 28.973,18.754C29.105,18.748 29.273,18.747 29.448,18.745C30.171,18.738 30.696,18.723 30.862,18.584C31,18.469 31.042,17.837 30.938,17.438C30.881,17.224 30.787,17.083 30.67,17.029L29.036,16.038L30.701,15.899L30.735,15.889C30.934,15.789 30.97,15.291 30.946,15.001C30.929,14.801 30.867,14.444 30.642,14.296L28.125,12.755L28.271,11.465L25.604,11.543L25.621,11.643C25.644,11.788 25.624,11.963 25.602,12.149C25.559,12.531 25.509,12.963 25.856,13.259C25.976,13.361 26.375,13.59 26.797,13.831C27.063,13.984 27.406,14.18 27.575,14.29C27.467,14.327 27.315,14.318 27.167,14.31C27.071,14.304 26.971,14.299 26.882,14.306L26.754,14.314C26.334,14.342 25.946,14.371 25.829,14.426C25.627,14.522 25.565,14.936 25.594,15.281C25.608,15.454 25.669,15.873 25.937,16.043L27.573,17.036L25.859,17.187L25.825,17.197C25.658,17.281 25.634,17.616 25.634,17.809C25.634,18.125 25.706,18.558 25.924,18.745L27.619,19.793C27.556,19.794 27.492,19.797 27.429,19.798C26.879,19.812 26.31,19.827 25.805,19.973L25.778,19.987C25.652,20.082 25.643,20.734 25.731,21.104C25.784,21.327 25.874,21.47 25.986,21.523L27.623,22.515L25.973,22.666L25.942,22.674C25.594,22.84 25.725,23.76 25.814,24.029L25.828,24.073L27.694,25.278L26.048,25.389C25.936,25.395 25.881,25.457 25.838,25.513C25.736,25.649 25.716,26.064 25.725,26.687L22.928,28.121C22.526,26.781 22.026,25.473 21.44,24.227C20.905,23.117 20.325,22.049 19.616,20.782C18.982,19.686 18.377,18.607 17.949,17.469C17.494,16.244 17.272,15.013 17.289,13.806C17.281,12.04 17.755,10.261 18.66,8.658C19.335,7.45 20.154,6.435 21.099,5.635C22.315,4.575 23.78,3.809 25.335,3.418C25.403,3.4 25.471,3.386 25.539,3.371L25.601,3.358C27.314,2.978 29.114,3.038 30.807,3.528C31.353,3.686 31.877,3.886 32.366,4.122C34.263,5.023 35.908,6.558 36.999,8.449C38.197,10.474 38.699,12.799 38.413,14.999C38.278,16.152 37.912,17.369 37.328,18.611C37.019,19.238 36.663,19.862 36.318,20.466C36.1,20.849 35.882,21.233 35.67,21.623C35.027,22.81 33.998,25.213 33.66,26.003C33.6,26.143 33.562,26.233 33.546,26.266C33.191,27.089 32.681,28.272 32.517,28.649L29.277,30.223L22.526,33.501C21.759,33.811 21.307,34.598 21.426,35.422C21.542,36.23 22.207,36.854 23.065,36.954L27.329,37.104L21.883,40.192L23.155,42.998L34.252,37.646C35.135,37.238 35.527,36.211 35.146,35.309C34.893,34.709 34.312,34.295 33.63,34.228L29.608,33.244L34.727,31.075C34.852,31.018 34.859,31.013 36.174,27.988C36.234,27.849 36.296,27.705 36.362,27.553C36.363,27.551 36.374,27.525 36.394,27.479C36.797,26.527 37.799,24.195 38.388,23.107C38.582,22.747 38.784,22.392 38.987,22.038L39.004,22.009C39.355,21.395 39.752,20.699 40.115,19.961C40.849,18.399 41.31,16.857 41.483,15.382C41.856,12.51 41.211,9.489 39.667,6.877C38.271,4.459 36.15,2.487 33.699,1.321"></path>
</svg>
'''

CORNER = '''<svg class="h-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 11 11" fill="none"><path d="M9.5 0.5H0.5V9.5" class="stroke-current" stroke="currentColor" stroke-linecap="round"></path></svg>'''

STAR = '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" fill="none" aria-hidden="true">
<path d="M8.08887 0.989258C8.31973 0.427753 9.07211 0.326095 9.44336 0.806641C9.4865 0.862481 9.52201 0.923989 9.54883 0.989258V0.991211L11.3418 5.33105L11.458 5.61328L11.7637 5.63867L16.4102 6.0127V6.01367C17.0215 6.06538 17.3475 6.75913 16.9971 7.2627C16.9591 7.31722 16.9145 7.36673 16.8643 7.41016L15.5156 8.57422L15.5205 8.57324L13.3232 10.4697L13.0938 10.668L13.1631 10.9629L14.2432 15.5381L14.2441 15.54C14.3864 16.1331 13.8328 16.6577 13.248 16.4844C13.1823 16.4648 13.1191 16.4371 13.0605 16.4014H13.0615L9.08301 13.9531L8.82031 13.792L8.55859 13.9531L4.5791 16.4004C4.05796 16.7169 3.38971 16.3508 3.37598 15.7412C3.37445 15.6735 3.38178 15.6059 3.39746 15.54V15.5391L4.48145 10.9629L4.55176 10.668L4.32129 10.4697L0.776367 7.41016C0.312197 7.00899 0.456622 6.25511 1.03613 6.05371C1.09645 6.03279 1.1591 6.01868 1.22266 6.0127L1.22363 6.01367L5.87305 5.63867L6.17773 5.61328L6.29492 5.33105L8.08789 0.991211L8.08887 0.989258Z" fill="currentColor" stroke="#292524" stroke-width="1"></path>
</svg>'''

ROCKET_SVG = ""  # unused
METEOR = ""
ARROW = '''<svg class="arrow" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>'''
CHEVRON = '''<svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>'''

GH_ICON = '''<svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0 0 16 8c0-4.42-3.58-8-8-8z"/></svg>'''

WEB_ICON = '''<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>'''

PAGES = [
    ("installation", "Installation"),
    ("explorer", "Explorer page"),
    ("files-table", "Files table"),
    ("form-picker", "Form picker"),
    ("authorization", "Authorization"),
    ("configuration", "Configuration"),
]


def colorize(code: str, lang: str) -> str:
    import html as _html
    import re
    esc = _html.escape(code)
    if lang in ("bash", "shell"):
        lines = []
        for line in esc.split("\n"):
            line = re.sub(
                r"^(\s*)(composer|php|npm|npx)(\b)",
                r'\1<span class="tok-cmd">\2</span>\3',
                line,
            )
            line = re.sub(
                r"(--[a-zA-Z][\w-]*)",
                r'<span class="tok-flag">\1</span>',
                line,
            )
            line = re.sub(
                r"(&quot;.*?&quot;)",
                r'<span class="tok-str">\1</span>',
                line,
            )
            lines.append(line)
        return "\n".join(lines)
    if lang in ("php", "css"):
        esc = re.sub(r"(?m)(//.*?$)", r'<span class="tok-cmt">\1</span>', esc)
        esc = re.sub(
            r"\b(use|return|function|public|final|class|implements|null|bool|true|false|new)\b",
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


GUIDE_BODIES = {
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

<figure class="shot">
  <img src="../images/cover.png" alt="Filament File Explorer cover" width="1200" height="750" loading="lazy" decoding="async">
  <figcaption><span>Theme + assets ready</span><span class="tag">Cover</span></figcaption>
</figure>

<h2>Fast path</h2>
<ol>
  <li>Model: <code>use HasFileExplorer</code></li>
  <li><code>php artisan filament-file-explorer:make-folder-migration {{table}}</code> → migrate</li>
  <li><code>php artisan filament-file-explorer:make-page {{Resource}}</code></li>
  <li>Optional: <code>php artisan filament-file-explorer:make-authorizer</code></li>
</ol>
<div>__HONEY_NEXT__</div>
""",
    "explorer": f"""
<p>Full Finder-style surface: sidebar tree, grid/list, toolbar, clipboard, Get Info, context menu.</p>
<figure class="shot"><img src="../images/explorer-grid.png" loading="lazy" decoding="async" alt="Explorer grid view"><figcaption><span>Grid view</span><span class="tag">MIME</span></figcaption></figure>
<figure class="shot"><img src="../images/explorer-list.png" loading="lazy" decoding="async" alt="Explorer list view"><figcaption><span>List view</span><span class="tag">Rows</span></figcaption></figure>
<figure class="shot"><img src="../images/explorer-context-menu.png" loading="lazy" decoding="async" alt="Context menu"><figcaption><span>Context menu</span><span class="tag">Actions</span></figcaption></figure>
<figure class="shot"><img src="../images/explorer-get-info.png" loading="lazy" decoding="async" alt="Get Info panel"><figcaption><span>Get Info</span><span class="tag">Inspector</span></figcaption></figure>
{code_panel("blade", '''@livewire('filament-file-explorer::file-explorer', [
    'model' => $record,
    'authorizer' => YourAuthorizer::class,
])''', "php")}
""",
    "files-table": """
<p>Flat Filament table of media in the <code>file-explorer</code> collection — filters, bulk actions, downloads.</p>
<figure class="shot"><img src="../images/files-table.png" loading="lazy" decoding="async" alt="Files table"><figcaption><span>Files table</span><span class="tag">Filament</span></figcaption></figure>
""",
    "form-picker": """
<p>Modal picker to attach existing media from the explorer into a form field.</p>
<figure class="shot"><img src="../images/form-picker-modal.svg" alt="Form picker"><figcaption><span>Picker modal</span><span class="tag">Form</span></figcaption></figure>
""",
    "authorization": f"""
<p>Implement <code>FileExplorerAuthorizer</code> (or generate one) to gate browse / upload / rename / delete per owner model.</p>
{code_panel("Authorizer.php", '''final class QuestionFileExplorerAuthorizer implements FileExplorerAuthorizer
{{
    public function canBrowse(Model $owner, ?Authenticatable $user = null): bool
    {{
        return $user?->can('view', $owner) ?? false;
    }}
    // canUpload, canRename, canDelete, ...
}}''', "php")}
""",
    "configuration": f"""
<p>Publish config to tune collection name, disk, upload rules, and UI defaults.</p>
{code_panel("publish", "php artisan vendor:publish --tag=filament-file-explorer-config", "bash")}
""",
}


def honey_btn(href: str, label: str) -> str:
    return f'''<a data-btn-honey href="{href}" class="btn-honey" aria-label="{label}">
  <span class="label">{label}</span>
  <span class="orb" aria-hidden="true">{CHEVRON}</span>
</a>'''


def ghost_btn(href: str, label: str) -> str:
    return f'''<a data-btn-ghost href="{href}" class="btn-ghost" aria-label="{label}" target="_blank" rel="noopener">
  <span class="swap" aria-hidden="true">{GH_ICON}</span>
  <span class="label">{label}</span>
  {ARROW}
</a>'''


def plain_btn(href: str, label: str) -> str:
    return f'''<a class="btn-plain" href="{href}">{label} {CHEVRON}</a>'''


GUIDE_BODIES["installation"] = GUIDE_BODIES["installation"].replace(
    "__HONEY_NEXT__",
    honey_btn("explorer.html", "Next: Explorer"),
)


def nav_link(href: str, label: str, active: bool = False) -> str:
    cls = "nav-link is-active" if active else "nav-link"
    return f'''<a class="{cls}" href="{href}">
  <span class="corner corner-tl">{CORNER}</span>
  <span>{label}</span>
  <span class="corner corner-br">{CORNER}</span>
</a>'''


def header(prefix: str, active: str | None = None) -> str:
    home = f"{prefix}index.html" if prefix else "./"
    docs = f"{prefix}guide/installation.html"
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
    return f'''
<footer class="site-footer">
  <div class="wrap footer-row">
    <div>MIT © <a href="https://ardavanshamroshan.ir" style="font-weight:700;color:var(--color-stone-800)">Ardavan Shamroshan</a></div>
    <div class="footer-links">
      <a href="{home}">Home</a>
      <a href="{prefix}guide/installation.html">Docs</a>
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
    import re
    s = re.sub(r"/\*.*?\*/", "", s, flags=re.S)
    s = re.sub(r"\s+", " ", s)
    s = re.sub(r"\s*([{}:;,>~+])\s*", r"\1", s)
    return s.strip()


def minify_html(s: str) -> str:
    import re
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


def sidebar(active: str) -> str:
    links = "\n".join(
        f'<a href="{slug}.html" class="{"is-active" if slug == active else ""}">{label}</a>'
        for slug, label in PAGES
    )
    return f'''
<aside class="docs-side">
  <h4>Documentation</h4>
  <nav aria-label="Guide pages">{links}</nav>
  <h4 style="margin-top:1rem">Resources</h4>
  <nav>
    <a href="../">Home</a>
    <a href="https://github.com/ardavanshamroshan/filament-file-explorer">Repository</a>
    <a href="https://packagist.org/packages/ardavan/filament-file-explorer">Packagist</a>
    <a href="https://ardavanshamroshan.ir">Author</a>
  </nav>
</aside>
'''


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
          {honey_btn("guide/installation.html", "Get started")}
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
        <img src="images/cover.png" alt="Filament File Explorer cover" width="1200" height="750" fetchpriority="high" decoding="async">
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
        {plain_btn("guide/explorer.html", "Explorer guide")}
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
          <p>Grid, list, menus, and the files table.</p>
        </div>
      </div>
      <div class="bento">
        <figure class="shot shot-lg">
          <img src="images/explorer-grid.png" loading="lazy" decoding="async" alt="Grid view">
          <figcaption><span>Grid view</span><span class="tag">Finder</span></figcaption>
        </figure>
        <figure class="shot shot-md">
          <img src="images/explorer-list.png" loading="lazy" decoding="async" alt="List view">
          <figcaption><span>List view</span><span class="tag">Rows</span></figcaption>
        </figure>
        <figure class="shot shot-md">
          <img src="images/files-table.png" loading="lazy" decoding="async" alt="Files table">
          <figcaption><span>Files table</span><span class="tag">Filament</span></figcaption>
        </figure>
        <figure class="shot shot-sm">
          <img src="images/explorer-context-menu.png" loading="lazy" decoding="async" alt="Context menu">
          <figcaption><span>Menus</span><span class="tag">Actions</span></figcaption>
        </figure>
        <figure class="shot shot-wide">
          <img src="images/explorer-get-info.png" loading="lazy" decoding="async" alt="Get Info">
          <figcaption><span>Get Info</span><span class="tag">Inspector</span></figcaption>
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
          <p>Full guide covers theme setup, authorizers, and generators.</p>
        </div>
        <div class="cta-row">
          {honey_btn("guide/installation.html", "Get started")}
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


GUIDE_LEADS = {
    "installation": "Composer, Media Library, theme source, then the plugin.",
    "explorer": "Full Finder surface: tree, grid/list, toolbar, clipboard, Get Info.",
    "files-table": "Flat Filament table over the file-explorer media collection.",
    "form-picker": "Modal picker to attach explorer media into forms.",
    "authorization": "Gate browse / upload / rename / delete with a small authorizer contract.",
    "configuration": "Publish config for collection, disk, upload rules, UI defaults.",
}


def build_guides():
    for slug, label in PAGES:
        body = f'''
<main>
  <section class="docs-hero">
    <div class="wrap docs-hero-grid">
      <div>
        <div class="eyebrow">
          <img src="../images/logo.png" alt="" width="16" height="16" style="width:1rem;height:1rem;border-radius:.25rem">
          Documentation · {label}
        </div>
        <h1>{label}</h1>
        <p class="lead">{GUIDE_LEADS[slug]}</p>
        <div class="hero-actions cta-row">
          {honey_btn("installation.html" if slug != "installation" else "explorer.html", "Continue" if slug != "installation" else "Next: Explorer")}
          {ghost_btn("https://github.com/ardavanshamroshan/filament-file-explorer", "GitHub")}
        </div>
      </div>
      <div class="hero-art">
        <img src="../images/cover.png" alt="Filament File Explorer cover" width="1200" height="750" loading="lazy" decoding="async">
      </div>
    </div>
  </section>
  <div class="wrap docs-shell">
    {sidebar(slug)}
    <article class="docs-main">
      {GUIDE_BODIES[slug]}
    </article>
  </div>
</main>
'''
        html = shell(
            f"{label} — Filament File Explorer",
            f"{label} documentation for Filament File Explorer",
            body + author_block("../"),
            prefix="../",
            active="docs",
        )
        (GUIDE / f"{slug}.html").write_text(html, encoding="utf-8")


if __name__ == "__main__":
    css = (ROOT / "assets" / "site.css").read_text(encoding="utf-8")
    js = (ROOT / "assets" / "site.js").read_text(encoding="utf-8")
    (ROOT / "assets" / "site.min.css").write_text(minify_css(css), encoding="utf-8")
    (ROOT / "assets" / "site.min.js").write_text(js.strip() + "\n", encoding="utf-8")
    build_index()
    build_guides()
    print("Built minified docs")
