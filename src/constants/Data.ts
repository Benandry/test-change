import {
  backend,
  database,
  frontend,
  express_icon,
  symfony_icon,
  react_icon,
  angular_icon,
  html_icon,
  css_icon,
  bootstrap_icon,
  tailwind_icon,
  js_icon,
  ts_icon,
  php_icon,
  python_icon,
  mysql_icon,
  postgresql_icon,
  sqlite_icon,
  mongo_icon,
} from "../assets";

export const navLinks = [
  {
    name: "Competences",
    path: "/#skills",
  },
  {
    name: "A propos",
    path: "/#me",
  },
  {
    name: "Projets",
    path: "/#projects",
  },

  {
    name: "technologies",
    path: "/#tech",
  },
  {
    name: "Formations",
    path: "/#graduates",
  },
  {
    name: "Contacts",
    path: "/#contact",
  },
];

export const skills = [
  {
    id: "skill-2",
    title: "Développement front-end",
    logo: backend,
    text: `En tant que développeur front-end ,
       Mon engagement envers la qualité du code et mon souci du détail se reflètent dans chaque ligne de code que j'écris.`,
  },
  {
    id: "skill-3",
    title: "Développement back-end",
    logo: frontend,
    text: `
      En tant que développeur backend , 
      je suis déterminé à bâtir les bases solides qui rendent les applications web performantes et fiables. 
      
      `,
  },
  {
    id: "skill-4",
    title: "Conception de base de données",
    logo: database,
    text: `Je suis déterminé à concevoir des structures de données robustes qui sont au cœur de chaque projet
    `,
  },
];

export const language_programming = [
  {
    icon: js_icon,
    title: "javascript",
    progress: 80,
  },
  {
    icon: ts_icon,
    title: "typescript",
    progress: 75,
  },
  {
    icon: php_icon,
    title: "php",
    progress: 75,
  },
  {
    icon: python_icon,
    title: "python",
    progress: 75,
  },
];

export const framework = [
  {
    icon: express_icon,
    title: "Express JS",
    progress: 80,
  },
  {
    icon: symfony_icon,
    title: "Symfony",
    progress: 80,
  },
  {
    icon: react_icon,
    title: "React",
    progress: 80,
  },
  {
    icon: angular_icon,
    title: "Angular",
    progress: 75,
  },
];

export const stylePaging = [
  {
    icon: html_icon,
    title: "HTML",
    progress: 95,
  },
  {
    icon: css_icon,
    title: "CSS",
    progress: 90,
  },
  {
    icon: bootstrap_icon,
    title: "BootStrap",
    progress: 80,
  },
  {
    icon: tailwind_icon,
    title: "Tailwind CSS",
    progress: 75,
  },
];

export const sgbd = [
  {
    icon: mysql_icon,
    title: "MYSQL",
    progress: 80,
  },
  {
    icon: postgresql_icon,
    title: "PostgreSQL",
    progress: 70,
  },
  {
    icon: sqlite_icon,
    title: "SQLITE",
    progress: 70,
  },
  {
    icon: mongo_icon,
    title: "Mongo db",
    progress: 60,
  },
];
