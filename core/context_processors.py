from django.conf import settings


def futturu_branding(request):
    """Context processor para adicionar branding da Futturu em todos os templates."""
    return {
        'FUTTURL_LINK': getattr(settings, 'FUTTURL_LINK', 'https://futturu.com.br'),
    }
