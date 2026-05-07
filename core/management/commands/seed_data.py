from django.core.management.base import BaseCommand
from django.contrib.auth import get_user_model
from jobs.models import Categoria, Bairro, Oportunidade
import random

User = get_user_model()


class Command(BaseCommand):
    help = 'Popular banco de dados com dados iniciais para desenvolvimento'

    def handle(self, *args, **kwargs):
        self.stdout.write('Iniciando seed de dados...')

        # Criar categorias
        categorias_data = [
            ('Serviços Gerais', 'hammer'),
            ('Vagas CLT', 'briefcase'),
            ('Estágios', 'academic-cap'),
            ('Bicos/Freelas', 'cash'),
            ('Aulas/Reforço', 'book-open'),
            ('Reformas', 'wrench'),
            ('Beleza e Estética', 'sparkles'),
            ('Tecnologia', 'computer'),
            ('Entregas', 'truck'),
            ('Cuidador/Acompanhante', 'heart'),
        ]
        
        categorias = {}
        for nome, icone in categorias_data:
            cat, created = Categoria.objects.get_or_create(
                nome=nome,
                defaults={'icone': icone}
            )
            categorias[nome] = cat
            if created:
                self.stdout.write(f'  ✓ Categoria criada: {nome}')

        # Criar bairros com coordenadas aproximadas de Belém
        bairros_data = [
            ('Centro', 'belem', -1.4558, -48.5039),
            ('Cidade Velha', 'belem', -1.4595, -48.5069),
            ('Campina', 'belem', -1.4572, -48.5028),
            ('Reduto', 'belem', -1.4503, -48.5089),
            ('Nazaré', 'belem', -1.4478, -48.4989),
            ('Fátima', 'belem', -1.4436, -48.4928),
            ('Batalha', 'belem', -1.4392, -48.4878),
            ('São Brás', 'belem', -1.4456, -48.4856),
            ('Pedreira', 'belem', -1.4389, -48.4789),
            ('Marco', 'belem', -1.4325, -48.4712),
            ('Telégrafo', 'belem', -1.4689, -48.4923),
            ('Guamá', 'belem', -1.4512, -48.4789),
            ('Terra Firme', 'belem', -1.4589, -48.4656),
            ('Marambaia', 'belem', -1.4756, -48.4889),
            ('Montanha', 'belem', -1.4823, -48.4756),
            ('Parque Verde', 'belem', -1.4289, -48.4589),
            ('Cabanagem', 'belem', -1.4156, -48.4456),
            ('Centro', 'ananindeua', -1.3689, -48.3756),
            ('Jardim Felicidade', 'ananindeua', -1.3556, -48.3689),
            ('Coqueiro', 'ananindeua', -1.3423, -48.3856),
            ('Centro', 'marituba', -1.3156, -48.3389),
            ('Jaderlândia', 'marituba', -1.3289, -48.3256),
        ]
        
        bairros = {}
        for nome, cidade, lat, lng in bairros_data:
            bairro, created = Bairro.objects.get_or_create(
                nome=nome,
                cidade=cidade,
                defaults={'latitude': lat, 'longitude': lng}
            )
            bairros[f'{cidade}_{nome}'] = bairro
            if created:
                self.stdout.write(f'  ✓ Bairro criado: {nome} - {cidade}')

        # Criar usuário admin se não existir
        if not User.objects.filter(username='admin').exists():
            admin = User.objects.create_superuser(
                username='admin',
                email='admin@oportunidadesbelem.com.br',
                password='admin123',
                whatsapp='5591999999999',
                user_type='contratante',
                is_verified=True
            )
            self.stdout.write('  ✓ Usuário admin criado (senha: admin123)')

        # Criar usuários de exemplo
        usuarios_exemplo = [
            ('joao_prestador', 'João Silva', 'joao@email.com', 'prestador', 'belem_centro', '5591988887777'),
            ('maria_contratante', 'Maria Santos', 'maria@email.com', 'contratante', 'belem_nazare', '5591977776666'),
            ('pedro_prestador', 'Pedro Oliveira', 'pedro@email.com', 'prestador', 'ananindeua_centro', '5591966665555'),
            ('ana_prestadora', 'Ana Costa', 'ana@email.com', 'prestador', 'belem_batalha', '5591955554444'),
        ]
        
        for username, nome, email, user_type, bairro, whatsapp in usuarios_exemplo:
            if not User.objects.filter(username=username).exists():
                user = User.objects.create_user(
                    username=username,
                    email=email,
                    password='123456',
                    first_name=nome.split()[0],
                    last_name=' '.join(nome.split()[1:]) if len(nome.split()) > 1 else '',
                    whatsapp=whatsapp,
                    bairro=bairro,
                    user_type=user_type,
                    is_verified=True
                )
                self.stdout.write(f'  ✓ Usuário criado: {username}')

        # Criar oportunidades de exemplo
        if User.objects.count() > 1 and Oportunidade.objects.count() == 0:
            usuarios = list(User.objects.filter(is_superuser=False))
            lista_categorias = list(Categoria.objects.all())
            lista_bairros = [
                'Centro', 'Nazaré', 'Batalha', 'São Brás', 'Terra Firme',
                'Cabanagem', 'Guamá', 'Pedreira', 'Marco', 'Telégrafo'
            ]
            
            oportunidades_data = [
                ('Preciso de Eletricista para Residência', 'Serviços Gerais', 'Centro', 'R$ 150-300', 'Preciso de um eletricista experiente para reparos em residência no centro de Belém. Serviço inclui troca de fiação e instalação de tomadas.'),
                ('Vaga para Vendedor(a) de Loja', 'Vagas CLT', 'Nazaré', 'R$ 1500 + comissão', 'Loja de roupas no shopping busca vendedor(a) com experiência. Horário comercial, benefícios incluem VT e VR.'),
                ('Estágio em Desenvolvimento Web', 'Estágios', 'São Brás', 'R$ 800 + bolsa', 'Empresa de tecnologia busca estudante de TI para estágio em desenvolvimento frontend. Conhecimentos em HTML, CSS e JavaScript.'),
                ('Diária de Pintura', 'Reformas', 'Terra Firme', 'R$ 200/diária', 'Preciso de pintor para reforma de apartamento. 3 dias de serviço. Material incluso.'),
                ('Aula Particular de Matemática', 'Aulas/Reforço', 'Batalha', 'R$ 50/hora', 'Professora particular para ensino fundamental e médio. Matemática e física. Aulas presenciais ou online.'),
                ('Entregador de App', 'Entregas', 'Belém (toda)', 'R$ 2000-4000', 'Trabalhe como entregador parceiro. Flexibilidade de horários. Necessário bicicleta ou moto.'),
                ('Cuidadora de Idosos', 'Cuidador/Acompanhante', 'Marco', 'R$ 1800', 'Família busca cuidadora para idoso acamado. Experiência comprovada necessária. Plantão 12x36.'),
                ('Freelance Design Gráfico', 'Tecnologia', 'Remoto', 'A combinar', 'Designer gráfico para criação de identidade visual e material de marketing. Portfólio necessário.'),
                ('Manicure/Pedicure', 'Beleza e Estética', 'Guamá', 'R$ 1000 + comissão', 'Salão de beleza busca profissional com clientela. Ambiente climatizado e equipamentos inclusos.'),
                ('Ajudante de Obras', 'Serviços Gerais', 'Cabanagem', 'R$ 120/diária', 'Construtora busca ajudante de obras. Experiência não necessária, oferecemos treinamento.'),
            ]
            
            for titulo, cat_nome, bairro, valor, descricao in oportunidades_data:
                autor = random.choice(usuarios)
                categoria = Categoria.objects.filter(nome=cat_nome).first()
                
                oportunidade = Oportunidade.objects.create(
                    titulo=titulo,
                    descricao=descricao,
                    categoria=categoria,
                    bairro_nome=bairro,
                    valor_faixa=valor,
                    status='aprovado',
                    autor=autor
                )
                self.stdout.write(f'  ✓ Oportunidade criada: {titulo[:50]}...')

        self.stdout.write(self.style.SUCCESS('\n✓ Seed de dados concluído com sucesso!'))
        self.stdout.write(self.style.WARNING('\nDADOS DE ACESSO:'))
        self.stdout.write('  Admin: admin / admin123')
        self.stdout.write('  Usuários: senha padrão 123456')
