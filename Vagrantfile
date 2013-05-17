Vagrant.configure("2") do |config|
  config.vm.box = "precise32"
  config.vm.box_url = "http://files.vagrantup.com/precise32.box"

  config.vm.customize ["modifyvm", :id, "--memory", 1024]

  config.vm.network :forwarded_port, host: 8080, guest: 80

  config.vm.provision :shell, :path => "data/vagrant/bootstrap.sh"
end
