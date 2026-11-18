import re

path = 'C:/Users/batah/Documents/GitHub/TravianZ/GameEngine/Account.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace global declarations
content = re.sub(r'global\s+\$database,\s*\$form,\s*\$mailer,\s*\$generator,\s*\$session;', '', content)
content = re.sub(r'global\s+\$database;', '', content)
content = re.sub(r'global\s+\$database,\s*\$session,\s*\$form;', '', content)
content = re.sub(r'global\s+\$session,\s*\$database;', '', content)

# This is risky. I should be more targeted.
# Actually, I can just use edit_file to remove the global lines, and then replace specific usages.

# Or, even better, I'll use a search and replace for each method.

# Let's do it method by method with edit_file.
print("Manual refactoring needed.")
