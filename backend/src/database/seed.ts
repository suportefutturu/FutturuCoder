import pool from '../database';

export interface User {
  id: number;
  name: string;
  email: string;
  password: string;
  role: 'user' | 'admin';
  created_at: Date;
}

export interface Opportunity {
  id: number;
  title: string;
  description: string;
  company: string;
  whatsapp: string;
  category: string;
  neighborhood: string;
  salary?: string;
  is_approved: boolean;
  created_at: Date;
  user_id: number;
}

export const createTables = async () => {
  const client = await pool.connect();
  
  try {
    // Create users table
    await client.query(`
      CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);

    // Create opportunities table
    await client.query(`
      CREATE TABLE IF NOT EXISTS opportunities (
        id SERIAL PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        company VARCHAR(255) NOT NULL,
        whatsapp VARCHAR(50) NOT NULL,
        category VARCHAR(100) NOT NULL,
        neighborhood VARCHAR(100) NOT NULL,
        salary VARCHAR(100),
        is_approved BOOLEAN DEFAULT false,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        user_id INTEGER REFERENCES users(id)
      )
    `);

    console.log('Tables created successfully');
  } catch (error) {
    console.error('Error creating tables:', error);
    throw error;
  } finally {
    client.release();
  }
};

export const seedData = async () => {
  const client = await pool.connect();
  
  try {
    // Check if data already exists
    const result = await client.query('SELECT COUNT(*) FROM opportunities');
    if (parseInt(result.rows[0].count) > 0) {
      console.log('Data already seeded');
      return;
    }

    // Create admin user
    const bcrypt = require('bcryptjs');
    const hashedPassword = await bcrypt.hash('admin123', 10);
    
    await client.query(`
      INSERT INTO users (name, email, password, role) 
      VALUES ('Admin', 'admin@futturu.com', $1, 'admin')
    `, [hashedPassword]);

    // Seed opportunities
    const neighborhoods = ['Cidade Velha', 'Nazaré', 'Umarizal', 'Marco', 'Pedreira', 'Icoaraci', 'Ananindeua', 'Marituba'];
    const categories = ['Tecnologia', 'Vendas', 'Atendimento', 'Administrativo', 'Saúde', 'Educação'];
    
    const opportunities = [
      { title: 'Desenvolvedor Frontend', company: 'TechBelém', category: 'Tecnologia', neighborhood: 'Nazaré' },
      { title: 'Vendedor Loja', company: 'Comércio Pará', category: 'Vendas', neighborhood: 'Cidade Velha' },
      { title: 'Atendente Call Center', company: 'ConnectBelém', category: 'Atendimento', neighborhood: 'Umarizal' },
      { title: 'Assistente Administrativo', company: 'Serviços LTDA', category: 'Administrativo', neighborhood: 'Marco' },
      { title: 'Enfermeiro', company: 'Clínica Saúde', category: 'Saúde', neighborhood: 'Pedreira' },
      { title: 'Professor Particular', company: 'Educação Plus', category: 'Educação', neighborhood: 'Icoaraci' },
      { title: 'Analista de Sistemas', company: 'InfoTech', category: 'Tecnologia', neighborhood: 'Ananindeua' },
      { title: 'Gerente de Vendas', company: 'Varejo Forte', category: 'Vendas', neighborhood: 'Marituba' },
      { title: 'Recepcionista', company: 'Hotel Belém', category: 'Atendimento', neighborhood: 'Nazaré' },
      { title: 'Técnico em Enfermagem', company: 'Hospital Vida', category: 'Saúde', neighborhood: 'Cidade Velha' }
    ];

    for (const opp of opportunities) {
      await client.query(`
        INSERT INTO opportunities (title, description, company, whatsapp, category, neighborhood, salary, is_approved, user_id)
        VALUES ($1, $2, $3, $4, $5, $6, $7, true, 1)
      `, [
        opp.title,
        `Descrição da vaga de ${opp.title} na empresa ${opp.company}. Venha fazer parte do nosso time!`,
        opp.company,
        '5591999999999',
        opp.category,
        opp.neighborhood,
        'R$ 2.000 - R$ 4.000',
      ]);
    }

    console.log('Data seeded successfully');
  } catch (error) {
    console.error('Error seeding data:', error);
    throw error;
  } finally {
    client.release();
  }
};
