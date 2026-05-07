from django.urls import path
from . import views

urlpatterns = [
    path('', views.lista_oportunidades_view, name='lista_oportunidades'),
    path('oportunidade/<slug:slug>/', views.detalhe_oportunidade_view, name='detalhe_oportunidade'),
    path('nova/', views.criar_oportunidade_view, name='criar_oportunidade'),
    path('<slug:slug>/editar/', views.editar_oportunidade_view, name='editar_oportunidade'),
    path('<slug:slug>/excluir/', views.excluir_oportunidade_view, name='excluir_oportunidade'),
    path('api/mapa/', views.mapa_oportunidades_view, name='api_mapa'),
]
