from django.db import models
from django.utils.text import slugify
from django.conf import settings
import markdown


class Categoria(models.Model):
    """Categoria de oportunidades."""
    
    nome = models.CharField(max_length=100, unique=True)
    slug = models.SlugField(unique=True, blank=True)
    icone = models.CharField(
        max_length=50, 
        blank=True, 
        help_text='Nome do ícone (Heroicons/Lucide)'
    )
    descricao = models.TextField(blank=True)
    
    class Meta:
        verbose_name = 'Categoria'
        verbose_name_plural = 'Categorias'
        ordering = ['nome']
    
    def __str__(self):
        return self.nome
    
    def save(self, *args, **kwargs):
        if not self.slug:
            self.slug = slugify(self.nome)
        super().save(*args, **kwargs)


class Bairro(models.Model):
    """Bairro com localização para mapa."""
    
    nome = models.CharField(max_length=100)
    cidade = models.CharField(
        max_length=50,
        choices=[
            ('belem', 'Belém'),
            ('ananindeua', 'Ananindeua'),
            ('marituba', 'Marituba'),
            ('benevides', 'Benevides'),
        ],
        default='belem'
    )
    latitude = models.DecimalField(
        max_digits=9, 
        decimal_places=6, 
        null=True, 
        blank=True,
        help_text='Latitude para o mapa'
    )
    longitude = models.DecimalField(
        max_digits=9, 
        decimal_places=6, 
        null=True, 
        blank=True,
        help_text='Longitude para o mapa'
    )
    
    class Meta:
        verbose_name = 'Bairro'
        verbose_name_plural = 'Bairros'
        ordering = ['cidade', 'nome']
        unique_together = ['nome', 'cidade']
    
    def __str__(self):
        return f'{self.nome} - {self.get_cidade_display()}'


class Oportunidade(models.Model):
    """Oportunidade de trabalho/vaga."""
    
    STATUS_CHOICES = [
        ('pendente', 'Pendente de Aprovação'),
        ('aprovado', 'Aprovado'),
        ('rejeitado', 'Rejeitado'),
        ('expirado', 'Expirado'),
    ]
    
    titulo = models.CharField(max_length=200)
    slug = models.SlugField(unique=True, blank=True)
    descricao = models.TextField(help_text='Descrição da oportunidade')
    categoria = models.ForeignKey(
        Categoria, 
        on_delete=models.SET_NULL, 
        null=True, 
        related_name='oportunidades'
    )
    bairro_nome = models.CharField(
        max_length=100,
        help_text='Bairro onde é a oportunidade'
    )
    valor_faixa = models.CharField(
        max_length=100,
        blank=True,
        help_text='Faixa de valor (ex: R$ 1000-2000, A combinar)'
    )
    status = models.CharField(
        max_length=20,
        choices=STATUS_CHOICES,
        default='pendente'
    )
    autor = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE,
        related_name='oportunidades'
    )
    whatsapp_contato = models.CharField(
        max_length=15,
        blank=True,
        help_text='WhatsApp para contato (deixa em branco para usar o do autor)'
    )
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    expires_at = models.DateTimeField(
        null=True, 
        blank=True,
        help_text='Data de expiração automática'
    )
    
    class Meta:
        verbose_name = 'Oportunidade'
        verbose_name_plural = 'Oportunidades'
        ordering = ['-created_at']
        indexes = [
            models.Index(fields=['-created_at']),
            models.Index(fields=['status', '-created_at']),
        ]
    
    def __str__(self):
        return self.titulo
    
    def save(self, *args, **kwargs):
        if not self.slug:
            base_slug = slugify(self.titulo)
            slug = base_slug
            counter = 1
            while Oportunidade.objects.filter(slug=slug).exists():
                slug = f'{base_slug}-{counter}'
                counter += 1
            self.slug = slug
        
        if not self.whatsapp_contato and self.autor:
            self.whatsapp_contato = self.autor.whatsapp
        
        super().save(*args, **kwargs)
    
    def get_whatsapp_link(self):
        """Gerar link do WhatsApp para esta oportunidade."""
        message = f'Olá, vi sua vaga "{self.titulo}" no Oportunidades Belém e gostaria de mais informações.'
        whatsapp_number = self.whatsapp_contato or self.autor.whatsapp
        return f'https://wa.me/{whatsapp_number}?text={message.replace(" ", "%20")}'
    
    def is_active(self):
        """Verificar se a oportunidade está ativa."""
        return self.status == 'aprovado'
    
    def get_descricao_html(self):
        """Renderizar descrição como HTML (markdown simples)."""
        return markdown.markdown(self.descricao)
