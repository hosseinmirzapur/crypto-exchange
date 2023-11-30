<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td align="center">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                <td>
                                    @if(isset($url))
                                        <a href="{{ $url }}"
                                           class="button button-{{ $color ?? 'primary' }}"
                                           target="_blank"
                                           rel="noopener"
                                        >{{ $slot }}</a>
                                    @else
                                        <p class="action-field action"
                                        >{{ $slot }}</p>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
