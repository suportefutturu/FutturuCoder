/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        belem: {
          blue: '#002D62',
          white: '#FFFFFF',
          yellow: '#FFD700',
        },
        whatsapp: '#25D366',
      },
    },
  },
  plugins: [],
}
