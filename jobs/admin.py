from django.contrib import admin
from .models import Categoria, Bairro, Oportunidade


@admin.register(Categoria)
class CategoriaAdmin(admin.ModelAdmin):
    """Admin para Categorias."""
    
    list_display = ['nome', 'slug', 'icone']
    search_fields = ['nome', 'descricao']
    prepopulated_fields = {'slug': ('nome',)}


@admin.register(Bairro)
class BairroAdmin(admin.ModelAdmin):
    """Admin para Bairros."""
    
    list_display = ['nome', 'cidade', 'latitude', 'longitude']
    list_filter = ['cidade']
    search_fields = ['nome']


@admin.register(Oportunidade)
class OportunidadeAdmin(admin.ModelAdmin):
    """Admin para Oportunidades com foco em moderação."""
    
    list_display = [
        'titulo', 
        'autor', 
        'bairro_nome', 
        'categoria', 
        'status', 
        'created_at',
        'is_active_badge'
    ]
    list_filter = ['status', 'categoria', 'bairro_nome', 'created_at']
    search_fields = ['titulo', 'descricao', 'autor__username', 'autor__email']
    prepopulated_fields = {'slug': ('titulo',)}
    date_hierarchy = 'created_at'
    ordering = ['-created_at']
    
    fieldsets = (
        ('Informações Básicas', {
            'fields': ('titulo', 'slug', 'autor', 'categoria')
        }),
        ('Detalhes', {
            'fields': ('descricao', 'bairro_nome', 'valor_faixa', 'whatsapp_contato')
        }),
        ('Status', {
            'fields': ('status', 'expires_at')
        }),
        ('Datas', {
            'fields': ('created_at', 'updated_at'),
            'classes': ('collapse',)
        }),
    )
    
    readonly_fields = ['created_at', 'updated_at']
    
    actions = ['aprovar_oportunidades', 'rejeitar_oportunidades']
    
    def is_active_badge(self, obj):
        """Badge visual para status ativo."""
        if obj.status == 'aprovado':
            return '✅ Ativo'
        elif obj.status == 'pendente':
            return '⏳ Pendente'
        elif obj.status == 'rejeitado':
            return '❌ Rejeitado'
        return '⚠️ Expirado'
    is_active_badge.short_description = 'Status'
    
    def aprovar_oportunidades(self, request, queryset):
        """Aprovar múltiplas oportunidades."""
        updated = queryset.update(status='aprovado')
        self.message_user(request, f'{updated} oportunidade(s) aprovada(s).')
    aprovar_oportunidades.short_description = 'Aprovar oportunidades selecionadas'
    
    def rejeitar_oportunidades(self, request, queryset):
        """Rejeitar múltiplas oportunidades."""
        updated = queryset.update(status='rejeitado')
        self.message_user(request, f'{updated} oportunidade(s) rejeitada(s).')
    rejeitar_oportunidades.short_description = 'Rejeitar oportunidades selecionadas'
