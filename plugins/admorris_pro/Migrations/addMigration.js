const fs = require('fs');
const path = require('path');

const migrationsDir = path.join(__dirname);
const templatePath = path.join(migrationsDir, 'migrationTemplate.txt');

function getTimestamp() {
  const now = new Date();
  const pad = (n) => n.toString().padStart(2, '0');
  return (
    now.getFullYear().toString() +
    pad(now.getMonth() + 1) +
    pad(now.getDate()) +
    pad(now.getHours()) +
    pad(now.getMinutes()) +
    pad(now.getSeconds())
  );
}

function createMigration(description = '') {
  const timestamp = getTimestamp();
  const className = `Migration${timestamp}`;
  const fileName = `${className}.php`;
  const filePath = path.join(migrationsDir, fileName);

  let template = fs.readFileSync(templatePath, 'utf8');
  template = template.replace(/\{\{TIMESTAMP\}\}/g, timestamp);
  if (description) {
    template = template.replace(
      /protected \$description = '';/,
      `protected $description = '${description.replace(/'/g, "\\'")}';`
    );
  }

  fs.writeFileSync(filePath, template);
  console.log(`Migration created: ${filePath}`);
}

// Usage: node addMigration.js "Description of migration"
const desc = process.argv[2] || '';
createMigration(desc);
