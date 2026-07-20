FROM docker:27-cli

# docker:cli already has the Docker client. Add Compose plugin + Python for the webhook.
RUN apk add --no-cache python3 docker-cli-compose

WORKDIR /app
COPY server.py /app/server.py

ENV WEBHOOK_PORT=9100
ENV DEPLOY_WORKSPACE=/workspace

EXPOSE 9100
CMD ["python3", "/app/server.py"]
