# ---------- Stage 1: Build the Vue app ----------
FROM node:20-alpine AS build

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

# Same-origin API: nginx proxies /api to the backend container
ENV VITE_API_URL=/api
RUN npm run build

# ---------- Stage 2: Serve with nginx ----------
FROM nginx:1.27-alpine AS runtime

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=build /app/dist /usr/share/nginx/html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
