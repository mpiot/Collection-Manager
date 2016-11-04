FROM nginx:stable

# Add config in the container for production deployment
# In dev the volume mount have priority on the copied file
COPY default.conf /etc/nginx/conf.d/default.conf
COPY nginx.conf /etc/nginx/nginx.conf
