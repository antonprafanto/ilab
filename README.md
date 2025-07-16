# ILab UNMUL - Integrated Laboratory Management System

Sistem manajemen laboratorium terpadu untuk Universitas Mulawarman yang mendukung:
- 8 fakultas internal UNMUL
- Penelitian mahasiswa, dosen, dan industri
- Booking peralatan lab modern (GC-MS, LC-MS/MS, AAS, FTIR, dll)
- Pembangunan IKN melalui layanan riset

## Tech Stack

- **Frontend**: React.js + TypeScript + Tailwind CSS + shadcn/ui
- **Backend**: Node.js + Express.js + JWT Authentication
- **Database**: MySQL
- **File Upload**: Multer
- **State Management**: Zustand

## Struktur Project

```
ilab-unmul/
├── client/          # React frontend application
├── server/          # Node.js backend API
├── shared/          # Shared types and utilities
├── docs/            # Documentation
└── tasks/           # Project planning and todos
```

## Quick Start

### Prerequisites
- Node.js 18+
- MySQL 8.0+
- npm atau yarn

### Installation

1. Clone repository
```bash
git clone <repository-url>
cd ilab-unmul
```

2. Install dependencies
```bash
npm run install:all
```

3. Setup environment variables
```bash
# Copy environment files
cp server/.env.example server/.env
cp client/.env.example client/.env
```

4. Setup database
```bash
# Import database schema
mysql -u root -p < docs/database/schema.sql
```

5. Start development
```bash
npm run dev
```

## Available Scripts

- `npm run dev` - Start both client and server in development mode
- `npm run build` - Build both client and server for production
- `npm run start` - Start production server
- `npm run install:all` - Install dependencies for all workspaces
- `npm run clean` - Clean all node_modules and build directories

## Development Workflow

1. Check `tasks/todo.md` for current development tasks
2. Create feature branches from `main`
3. Follow conventional commit messages
4. Submit pull requests for review

## Deployment

Target server: 103.187.89.240 (UNMUL Infrastructure)

## License

MIT License - Internal use for Universitas Mulawarman