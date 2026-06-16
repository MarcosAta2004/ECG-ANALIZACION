import os
import re

patterns = {
    'create': r'(<button[^>]*data-bs-target="#create.*?Modal"[^>]*>[\s\S]*?</button>)',
    'edit': r'(<button[^>]*btn-warning[^>]*data-bs-target="#edit.*?Modal"[^>]*>[\s\S]*?</button>)',
    'show': r'(<button[^>]*btn-info[^>]*data-bs-target="#show.*?Modal"[^>]*>[\s\S]*?</button>)',
    'delete': r'(@if\(\$[^<]+estado == 1\)[\s\S]*?<button[^>]*btn-danger[^>]*data-bs-target="#delete.*?Modal"[^>]*>[\s\S]*?</button>)',
    'activate': r'(@else\s*<button[^>]*btn-success[^>]*data-bs-target="#activate.*?Modal"[^>]*>[\s\S]*?</button>\s*@endif)',
    'action_group': r'(<div class="[^"]*list-user-action[^"]*">)([\s\S]*?)(</div>)',
 }

def process_file(path, prefix):
    with open(path, "r", encoding="utf-8") as f:
        content = f.read()

    # Wrap Create
    create_pattern = re.compile(r'(<button[^>]+(?:data-bs-target="#(?:create|modalCreate)[^"]*"|title="Registrar"[^>]+)>[\s\S]*?</button>)')
    if not f"can('{prefix}.store')" in content:
        content = create_pattern.sub(f"@can('{prefix}.store')\\n\\1\\n@endcan", content)

    # Wrap Edit
    edit_pattern = re.compile(r'(<button[^>]+btn-warning[^>]*>[\s\S]*?</button>|<a[^>]+btn-warning[^>]*>[\s\S]*?</a>)')
    if not f"can('{prefix}.update')" in content:
        content = edit_pattern.sub(f"@can('{prefix}.update')\\n\\1\\n@endcan", content)
        
    # Wrap Show
    show_pattern = re.compile(r'(<button[^>]+btn-info[^>]*>[\s\S]*?</button>|<a[^>]+btn-info[^>]*>[\s\S]*?</a>)')
    if not f"can('{prefix}.show')" in content and not 'imagenes.ecg.ver' in content:
        show_permission = f"{prefix}.show"
        if prefix == 'imagenes': show_permission = 'imagenes.ecg.ver'
        content = show_pattern.sub(f"@can('{show_permission}')\\n\\1\\n@endcan", content)

    # Wrap Activate/Deactivate (Delete)
    status_pattern = re.compile(r'(@if\(\$[^>]+estado == 1\)\s*<button[^>]+btn-danger[\s\S]*?</button>\s*@else\s*<button[^>]+btn-success[\s\S]*?</button>\s*@endif)')
    if not f"can('{prefix}.activar')" in content:
        content = status_pattern.sub(f"@can('{prefix}.activar')\\n\\1\\n@endcan", content)
        
    with open(path, "w", encoding="utf-8") as f:
        f.write(content)

files = [
    (r"c:\Users\marco\ECG-ANALIZACION\sistema\resources\views\imagenes\index.blade.php", "imagenes"),
    (r"c:\Users\marco\ECG-ANALIZACION\sistema\resources\views\predicciones\index.blade.php", "predicciones"),
    (r"c:\Users\marco\ECG-ANALIZACION\sistema\resources\views\ritmos_cardiacos\index.blade.php", "ritmos-cardiacos"),
    (r"c:\Users\marco\ECG-ANALIZACION\sistema\resources\views\mantenimientos\clasificaciones_arritmia\index.blade.php", "clasificaciones-arritmia"),
    (r"c:\Users\marco\ECG-ANALIZACION\sistema\resources\views\mantenimientos\grupos_cardiacos\index.blade.php", "grupos-cardiacos"),
    (r"c:\Users\marco\ECG-ANALIZACION\sistema\resources\views\mantenimientos\niveles_gravedad\index.blade.php", "niveles-gravedad"),
    (r"c:\Users\marco\ECG-ANALIZACION\sistema\resources\views\mantenimientos\prefijos_paciente\index.blade.php", "prefijos-paciente"),
    (r"c:\Users\marco\ECG-ANALIZACION\sistema\resources\views\seguridad\users\index.blade.php", "usuarios"),
    (r"c:\Users\marco\ECG-ANALIZACION\sistema\resources\views\seguridad\roles\index.blade.php", "roles"),
]

for p, prefix in files:
    if os.path.exists(p):
        print(f"Processing {prefix}")
        process_file(p, prefix)
