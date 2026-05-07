from django.contrib import admin
from django.contrib.auth.admin import UserAdmin
from .models import CustomUser


@admin.register(CustomUser)
class CustomUserAdmin(UserAdmin):
    """Admin customizado para o modelo CustomUser."""
    
    list_display = ['username', 'email', 'whatsapp', 'user_type', 'bairro', 'is_verified', 'is_staff', 'date_joined']
    list_filter = ['user_type', 'is_verified', 'is_staff', 'is_active', 'bairro']
    search_fields = ['username', 'email', 'whatsapp', 'first_name', 'last_name']
    ordering = ['-date_joined']
    
    fieldsets = UserAdmin.fieldsets + (
        ('Informações Adicionais', {
            'fields': ('whatsapp', 'bairro', 'user_type', 'is_verified')
        }),
    )
    
    add_fieldsets = UserAdmin.add_fieldsets + (
        ('Informações Adicionais', {
            'fields': ('whatsapp', 'bairro', 'user_type')
        }),
    )
