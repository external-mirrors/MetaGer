@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/suma-ev.png'))) }}"
                class="logo" alt="SUMA-EV Logo">
        </a>
    </td>
</tr>