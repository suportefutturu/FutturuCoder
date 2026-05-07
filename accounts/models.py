from django.db import models
from django.contrib.auth.models import AbstractUser
import re


class CustomUser(AbstractUser):
    """Custom User model extending Django's AbstractUser with additional fields."""
    
    USER_TYPE_CHOICES = [
        ('prestador', 'Prestador de Serviços/Profissional'),
        ('contratante', 'Contratante/Empresa'),
    ]
    
    BAIRRO_CHOICES = [
        ('belem_centro', 'Belém - Centro'),
        ('belem_cidade_velha', 'Belém - Cidade Velha'),
        ('belem_campina', 'Belém - Campina'),
        ('belem_reduto', 'Belém - Reduto'),
        ('belem_nazaré', 'Belém - Nazaré'),
        ('belem_fátima', 'Belém - Fátima'),
        ('belem_batalha', 'Belém - Batalha'),
        ('belem_são_brás', 'Belém - São Brás'),
        ('belem_pedreira', 'Belém - Pedreira'),
        ('belem_marco', 'Belém - Marco'),
        ('belem_telegrafo', 'Belém - Telégrafo'),
        ('belem_guamá', 'Belém - Guamá'),
        ('belem_terra_firme', 'Belém - Terra Firme'),
        ('belem_marambaia', 'Belém - Marambaia'),
        ('belem_montanha', 'Belém - Montanha'),
        ('belem_parque_verde', 'Belém - Parque Verde'),
        ('belem_cabanagem', 'Belém - Cabanagem'),
        ('belem_maguari', 'Belém - Maguari'),
        ('ananindeua_centro', 'Ananindeua - Centro'),
        ('ananindeua_jardim_felicidade', 'Ananindeua - Jardim Felicidade'),
        ('ananindeua_coqueiro', 'Ananindeua - Coqueiro'),
        ('ananindeua_paara', 'Ananindeua - PAARA'),
        ('marituba_centro', 'Marituba - Centro'),
        ('marituba_jaderlândia', 'Marituba - Jaderlândia'),
        ('benevides_centro', 'Benevides - Centro'),
    ]
    
    whatsapp = models.CharField(
        max_length=15, 
        unique=True, 
        help_text='WhatsApp com DDD (ex: 91987654321)'
    )
    bairro = models.CharField(
        max_length=50, 
        choices=BAIRRO_CHOICES,
        blank=True,
        null=True,
        help_text='Seu bairro ou região'
    )
    user_type = models.CharField(
        max_length=20, 
        choices=USER_TYPE_CHOICES,
        default='prestador'
    )
    is_verified = models.BooleanField(
        default=False,
        help_text='Usuário verificado'
    )
    
    class Meta:
        verbose_name = 'Usuário'
        verbose_name_plural = 'Usuários'
        ordering = ['-date_joined']
    
    def __str__(self):
        return f'{self.username} ({self.get_user_type_display()})'
    
    def clean_whatsapp(self):
        """Clean and validate WhatsApp number."""
        if self.whatsapp:
            # Remove all non-digit characters
            cleaned = re.sub(r'\D', '', self.whatsapp)
            # Ensure it has the country code
            if len(cleaned) == 11:  # DDD + 9 digits
                cleaned = '55' + cleaned
            elif len(cleaned) == 13 and not cleaned.startswith('55'):
                cleaned = '55' + cleaned
            self.whatsapp = cleaned
        return self.whatsapp
    
    def save(self, *args, **kwargs):
        self.clean_whatsapp()
        super().save(*args, **kwargs)
    
    def get_whatsapp_link(self, message='Olá!'):
        """Generate WhatsApp link for this user."""
        return f'https://wa.me/{self.whatsapp}?text={message}'
