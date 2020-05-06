# Create database board18 and user board18

FROM mysql:8.0.19

# One-time MySQL initialization script in /docker-entrypoint-initdb.d/100-board18.sql
# that will create the board18 database and user board18 with password board18
COPY utility/board18db.txt .
RUN sh -c 'echo "CREATE DATABASE board18;\n \
                 USE board18;\n" > /docker-entrypoint-initdb.d/100-board18.sql && \
           cat board18db.txt >> /docker-entrypoint-initdb.d/100-board18.sql && \
           echo "\n \
                 CREATE USER '\''board18'\''@'\''%'\'' IDENTIFIED BY '\''board18'\'';\n \
                 GRANT SELECT, INSERT, UPDATE ON board18.* TO '\''board18'\''@'\''%'\'';\n \
                 GRANT DELETE ON board18.auth_tokens TO '\''board18'\''@'\''%'\'';"  >> /docker-entrypoint-initdb.d/100-board18.sql'
